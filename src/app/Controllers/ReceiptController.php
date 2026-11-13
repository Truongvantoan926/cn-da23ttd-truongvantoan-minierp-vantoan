<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Supplier;
use DomainException;
use PDO;
use RuntimeException;
use Throwable;

final class ReceiptController extends Controller
{
    private const TYPES = ['IN', 'OUT'];
    private const MAX_ITEMS = 100;

    public function __construct()
    {
        AuthMiddleware::checkPermission('inventory.view');
    }

    public function index(): void
    {
        $this->renderIndex(null, $this->requestedPage());
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_receipts');
        $suppliers = (new Supplier())->findAll();
        $customers = (new Customer())->findAll();

        $this->renderForm($this->emptyValues(), null, $suppliers, $customers);
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_receipts');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm($this->emptyValues(), 'Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm($values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm($values, $error);
            return;
        }

        $pdo = Database::getInstance()->getConnection();
        $type = $values['type'];
        $partnerId = (int) $values['partner_id'];
        $employeeId = $values['employee_id'] !== '' ? (int) $values['employee_id'] : null;
        $receiptCode = ($type === 'IN' ? 'PN-' : 'PX-') . strtoupper(bin2hex(random_bytes(8)));
        try {
            $pdo->beginTransaction();

            $this->lockPartner($pdo, $type, $partnerId);
            if ($employeeId !== null) {
                $this->lockEmployee($pdo, $employeeId);
            }

            $receiptInsert = $pdo->prepare(
                'INSERT INTO inventory_receipts '
                . '(code, type, partner_id, employee_id, total_amount, note, payment_status, paid_at) '
                . 'VALUES (:code, :type, :partner_id, :employee_id, 0, :note, :payment_status, '
                . 'CASE WHEN :paid_immediately = 1 THEN NOW() ELSE NULL END)'
            );
            $receiptInsert->execute([
                'code' => $receiptCode,
                'type' => $type,
                'partner_id' => $partnerId,
                'employee_id' => $employeeId,
                'note' => $values['note'] !== '' ? $values['note'] : null,
                'payment_status' => $type === 'IN' ? 'paid' : 'pending',
                'paid_immediately' => $type === 'IN' ? 1 : 0,
            ]);

            $receiptId = (int) $pdo->lastInsertId();
            if ($receiptId < 1) {
                throw new RuntimeException('Không thể tạo phiếu kho.');
            }

            $productLock = $pdo->prepare(
                'SELECT id FROM products WHERE id = :product_id FOR UPDATE'
            );
            $stockCheck = $pdo->prepare(
                'SELECT stock_qty >= :qty FROM products WHERE id = :product_id FOR UPDATE'
            );
            $stockIn = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty + :qty WHERE id = :product_id'
            );
            $stockOut = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty - :qty WHERE id = :product_id'
            );
            $itemInsert = $pdo->prepare(
                'INSERT INTO inventory_receipt_items '
                . '(receipt_id, product_id, qty, price, total) '
                . 'VALUES (:receipt_id, :product_id, :qty, :price, '
                . 'ROUND(:qty_for_total * :price_for_total, 2))'
            );

            foreach ($this->nonEmptyItems($values['items']) as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (int) $item['qty'];

                if ($type === 'OUT') {
                    $stockCheck->execute([
                        'qty' => $quantity,
                        'product_id' => $productId,
                    ]);
                    $available = $stockCheck->fetchColumn();
                    if ($available === false) {
                        throw new RuntimeException('Sản phẩm không còn tồn tại.');
                    }

                    if ((int) $available !== 1) {
                        $pdo->rollBack();
                        $this->renderForm($values, 'Không đủ số lượng tồn kho để xuất.');
                        return;
                    }

                    $stockOut->execute(['qty' => $quantity, 'product_id' => $productId]);
                } else {
                    $productLock->execute(['product_id' => $productId]);
                    if ($productLock->fetchColumn() === false) {
                        throw new RuntimeException('Sản phẩm không còn tồn tại.');
                    }

                    $stockIn->execute(['qty' => $quantity, 'product_id' => $productId]);

                }

                $itemInsert->execute([
                    'receipt_id' => $receiptId,
                    'product_id' => $productId,
                    'qty' => $quantity,
                    'price' => $item['price'],
                    'qty_for_total' => $quantity,
                    'price_for_total' => $item['price'],
                ]);
            }

            $totalQuery = $pdo->prepare(
                'SELECT COALESCE(SUM(total), 0) '
                . 'FROM inventory_receipt_items WHERE receipt_id = :receipt_id'
            );
            $totalQuery->execute(['receipt_id' => $receiptId]);
            $total = $totalQuery->fetchColumn();

            $receiptUpdate = $pdo->prepare(
                'UPDATE inventory_receipts SET total_amount = :total WHERE id = :receipt_id'
            );
            $receiptUpdate->execute(['total' => $total, 'receipt_id' => $receiptId]);

            if ($type === 'IN') {
                (new Receipt())->recordInboundPayment($receiptId, (int) $_SESSION['user_id']);
            }

            (new ActivityLog())->log(
                'receipt.create',
                sprintf('Lập phiếu %s (#%d), loại %s.', $receiptCode, $receiptId, $type)
            );
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $this->redirect('/receipt/index');
    }

    public function confirmPayment(string $id): void
    {
        AuthMiddleware::checkPermission('inventory.manage_receipts');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderIndex('Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderIndex('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        if (!preg_match('/\A[1-9][0-9]*\z/', $id)) {
            $this->showNotFound();
            return;
        }

        try {
            $result = (new Receipt())->confirmPayment($id, (int) $_SESSION['user_id']);
        } catch (DomainException $exception) {
            $this->renderIndex($exception->getMessage());
            return;
        }

        if ($result === null) {
            $this->showNotFound();
            return;
        }
        if ($result === false) {
            $this->renderIndex('Phiếu kho này đã được thanh toán trước đó.');
            return;
        }

        $this->redirect('/receipt/index');
    }

    public function show(string $id): void
    {
        $receipt = (new Receipt())->getByIdWithDetails($id);
        if ($receipt === null) {
            $this->showNotFound();
            return;
        }

        $items = (new ReceiptItem())->getItemsByReceiptId($id);
        $this->render('inventory/receipts/show', [
            'items' => $items,
            'pageTitle' => 'Chi tiết phiếu ' . (string) $receipt['code'] . ' - Mini ERP',
            'receipt' => $receipt,
        ]);
    }

    public function contract(string $id): void
    {
        $receipt = (new Receipt())->getByIdWithDetails($id);
        if (
            $receipt === null
            || $receipt['type'] !== 'OUT'
            || (
                (int) $receipt['total_quantity'] < 10
                && (float) $receipt['total_amount'] < 50000000
            )
        ) {
            $this->showNotFound();
            return;
        }

        $items = (new ReceiptItem())->getItemsByReceiptId($id);
        $this->render('receipt/contract', [
            'items' => $items,
            'pageTitle' => 'Hợp đồng HĐ-' . (string) $receipt['code'] . ' - Mini ERP',
            'receipt' => $receipt,
        ]);
    }

    public function invoice(string $id): void
    {
        $receipt = (new Receipt())->getByIdWithDetails($id);
        if ($receipt === null) {
            $this->showNotFound();
            return;
        }

        $items = (new ReceiptItem())->getItemsByReceiptId($id);
        $sandboxPaymentUrl = null;
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        if (
            is_string($httpHost)
            && preg_match('/\A(?:[a-z0-9.-]+|\[[a-f0-9:]+\])(?::[0-9]{1,5})?\z/i', $httpHost) === 1
        ) {
            $sandboxPaymentUrl = 'http://' . $httpHost
                . rtrim(BASE_URL, '/')
                . '/receipt/sandboxPay/' . rawurlencode((string) $receipt['id']);
        }

        $this->render('receipt/invoice', [
            'items' => $items,
            'pageTitle' => 'Hóa đơn HD-' . (string) $receipt['code'] . ' - Mini ERP',
            'receipt' => $receipt,
            'sandboxPaymentUrl' => $sandboxPaymentUrl,
        ]);
    }

    public function sandboxPay(string $id): void
    {
        if (!preg_match('/\A[1-9][0-9]*\z/', $id)) {
            $this->showNotFound();
            return;
        }

        $receiptModel = new Receipt();
        $receipt = $receiptModel->getByIdWithDetails($id);
        if ($receipt === null || $receipt['type'] !== 'OUT') {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                http_response_code(405);
                header('Allow: GET, POST');
                $this->renderSandboxPay($receipt, false, 'Phương thức gửi yêu cầu không hợp lệ.');
                return;
            }

            $isPaid = $receipt['payment_status'] === 'paid';
            $this->renderSandboxPay(
                $receipt,
                $isPaid,
                $isPaid ? 'Phiếu này đã được thanh toán.' : null
            );
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderSandboxPay($receipt, false, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        try {
            $result = $receiptModel->confirmPayment($id, (int) $_SESSION['user_id']);
        } catch (DomainException $exception) {
            $this->renderSandboxPay($receipt, false, $exception->getMessage());
            return;
        }

        if ($result === null) {
            $this->showNotFound();
            return;
        }

        $receipt = $receiptModel->getByIdWithDetails($id);
        if ($receipt === null) {
            $this->showNotFound();
            return;
        }

        $this->renderSandboxPay(
            $receipt,
            true,
            $result ? 'Giao dịch Sandbox thành công.' : 'Phiếu này đã được thanh toán trước đó.'
        );
    }

    public function checkPaymentStatus(string $id): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, private');

        if (!preg_match('/\A[1-9][0-9]*\z/', $id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Mã phiếu không hợp lệ.']);
            return;
        }

        $receipt = (new Receipt())->getByIdWithDetails($id);
        if ($receipt === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy phiếu kho.']);
            return;
        }

        echo json_encode([
            'payment_status' => $receipt['payment_status'],
            'paid_at' => $receipt['paid_at'],
        ], JSON_THROW_ON_ERROR);
    }

    private function renderIndex(?string $error = null, int $page = 1): void
    {
        $result = (new Receipt())->getPaginatedWithDetails($page, self::ITEMS_PER_PAGE);
        $this->render('inventory/receipts/index', [
            'canManage' => AuthMiddleware::hasPermission('inventory.manage_receipts'),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Giao dịch kho - Mini ERP',
            'pagination' => $result['pagination'],
            'receipts' => $result['items'],
        ]);
    }

    private function renderSandboxPay(array $receipt, bool $success, ?string $message): void
    {
        $this->render('receipt/sandbox_pay', [
            'csrfToken' => $this->csrfToken(),
            'error' => $success ? null : $message,
            'pageTitle' => 'Sandbox thanh toán - Mini ERP',
            'receipt' => $receipt,
            'success' => $success,
            'successMessage' => $success ? $message : null,
        ]);
    }

    private function renderForm(
        array $values,
        ?string $error = null,
        ?array $suppliers = null,
        ?array $customers = null
    ): void
    {
        $this->render('inventory/receipts/create', [
            'customers' => $customers ?? (new Customer())->findAll(),
            'csrfToken' => $this->csrfToken(),
            'employees' => (new Employee())->findAll(),
            'error' => $error,
            'pageTitle' => 'Tạo phiếu kho - Mini ERP',
            'products' => (new Product())->findAll(),
            'suppliers' => $suppliers ?? (new Supplier())->findAll(),
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'type' => 'IN',
            'partner_id' => '',
            'employee_id' => '',
            'note' => '',
            'items' => [[
                'product_id' => '',
                'qty' => '',
                'price' => '',
            ]],
            'invalid_items' => false,
        ];
    }

    private function postedFields(): array
    {
        $values = $this->emptyValues();
        foreach (['type', 'partner_id', 'employee_id', 'note'] as $field) {
            $value = $_POST[$field] ?? '';
            $values[$field] = is_string($value) ? trim($value) : '';
        }

        $postedItems = $_POST['items'] ?? [];
        if (!is_array($postedItems) || count($postedItems) > self::MAX_ITEMS) {
            $values['invalid_items'] = true;
            return $values;
        }

        $values['items'] = [];
        foreach ($postedItems as $postedItem) {
            if (!is_array($postedItem)) {
                $values['invalid_items'] = true;
                continue;
            }

            $item = [];
            foreach (['product_id', 'qty', 'price'] as $field) {
                $value = $postedItem[$field] ?? '';
                $item[$field] = is_scalar($value) ? trim((string) $value) : '';
            }

            $values['items'][] = $item;
        }

        if ($values['items'] === []) {
            $values['items'][] = [
                'product_id' => '',
                'qty' => '',
                'price' => '',
            ];
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!in_array($values['type'], self::TYPES, true)) {
            return 'Loại phiếu không hợp lệ.';
        }

        if (!preg_match('/\A[1-9][0-9]*\z/', $values['partner_id'])) {
            return 'Vui lòng chọn đối tác phù hợp với loại phiếu.';
        }

        $partner = $values['type'] === 'IN'
            ? (new Supplier())->findById($values['partner_id'])
            : (new Customer())->findById($values['partner_id']);
        if ($partner === null) {
            return 'Đối tác được chọn không tồn tại.';
        }

        if ($values['employee_id'] !== '') {
            if (
                !preg_match('/\A[1-9][0-9]*\z/', $values['employee_id'])
                || (new Employee())->findById($values['employee_id']) === null
            ) {
                return 'Nhân viên lập phiếu không hợp lệ.';
            }
        }

        if (preg_match('/\A.{0,5000}\z/us', $values['note']) !== 1) {
            return 'Ghi chú không được vượt quá 5000 ký tự.';
        }

        if ($values['invalid_items'] || count($values['items']) > self::MAX_ITEMS) {
            return 'Danh sách sản phẩm không hợp lệ.';
        }

        $validItemCount = 0;
        $hasPositivePrice = false;
        $productModel = new Product();
        foreach ($values['items'] as $item) {
            if (
                $item['product_id'] === ''
                && $item['qty'] === ''
                && $item['price'] === ''
            ) {
                continue;
            }

            if (!preg_match('/\A[1-9][0-9]*\z/', $item['product_id'])) {
                return 'Vui lòng chọn sản phẩm cho từng dòng.';
            }

            if (
                !preg_match('/\A[1-9][0-9]{0,9}\z/', $item['qty'])
                || (strlen($item['qty']) === 10 && strcmp($item['qty'], '2147483647') > 0)
            ) {
                return 'Số lượng phải là số nguyên dương hợp lệ.';
            }

            if (!preg_match('/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', $item['price'])) {
                return 'Đơn giá phải là số không âm, tối đa 2 chữ số thập phân.';
            }
            if ((float) $item['price'] > 0) {
                $hasPositivePrice = true;
            }

            if ($productModel->findById($item['product_id']) === null) {
                return 'Một sản phẩm được chọn không còn tồn tại.';
            }

            $validItemCount++;
        }

        if ($validItemCount === 0) {
            return 'Phiếu kho phải có ít nhất một sản phẩm.';
        }
        if (!$hasPositivePrice) {
            return 'Tổng tiền phiếu kho phải lớn hơn 0.';
        }

        return null;
    }

    private function nonEmptyItems(array $items): array
    {
        return array_values(array_filter(
            $items,
            static fn (array $item): bool => $item['product_id'] !== ''
                || $item['qty'] !== ''
                || $item['price'] !== ''
        ));
    }

    private function lockPartner(PDO $pdo, string $type, int $partnerId): void
    {
        $table = $type === 'IN' ? 'suppliers' : 'customers';
        $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id = :partner_id FOR UPDATE");
        $statement->execute(['partner_id' => $partnerId]);

        if ($statement->fetchColumn() === false) {
            throw new RuntimeException('Đối tác không còn tồn tại.');
        }
    }

    private function lockEmployee(PDO $pdo, int $employeeId): void
    {
        $statement = $pdo->prepare('SELECT id FROM employees WHERE id = :employee_id FOR UPDATE');
        $statement->execute(['employee_id' => $employeeId]);

        if ($statement->fetchColumn() === false) {
            throw new RuntimeException('Nhân viên lập phiếu không còn tồn tại.');
        }
    }

    private function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    private function hasValidCsrfToken(): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $submittedToken = $_POST['csrf_token'] ?? null;

        return is_string($sessionToken)
            && is_string($submittedToken)
            && $sessionToken !== ''
            && hash_equals($sessionToken, $submittedToken);
    }

    private function csrfToken(): string
    {
        $token = $_SESSION['csrf_token'] ?? null;
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }

    private function showNotFound(): void
    {
        http_response_code(404);
        $this->renderIndex('Không tìm thấy phiếu kho được yêu cầu.');
    }
}
