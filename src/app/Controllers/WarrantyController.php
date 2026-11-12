<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductSerial;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class WarrantyController extends Controller
{
    private const MAX_SERIALS = 1000;
    private const MAX_SERIAL_TEXT_LENGTH = 100000;
    private const MAX_WARRANTY_MONTHS = 120;

    public function __construct()
    {
        AuthMiddleware::checkPermission('inventory.view');
    }

    public function index(): void
    {
        $error = $_SESSION['warranty_error'] ?? null;
        $success = $_SESSION['warranty_success'] ?? null;
        unset($_SESSION['warranty_error'], $_SESSION['warranty_success']);

        $this->renderIndex(
            is_string($error) ? $error : null,
            null,
            is_string($success) ? $success : null,
            $this->requestedPage()
        );
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('inventory.manage_warranty');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            return;
        }

        $values = [
            'product_id' => $this->postString('product_id'),
            'serial_numbers' => $this->postString('serial_numbers'),
            'warranty_months' => $this->postString('warranty_months'),
        ];

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderIndex('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $values);
            return;
        }

        $serialNumbers = $this->parseSerialNumbers($values['serial_numbers']);
        $error = $this->validateValues($values, $serialNumbers);
        if ($error !== null) {
            $this->renderIndex($error, $values);
            return;
        }

        $productId = (int) $values['product_id'];
        $warrantyMonths = (int) $values['warranty_months'];
        $pdo = Database::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            $productLock = $pdo->prepare(
                'SELECT id FROM products WHERE id = :product_id FOR UPDATE'
            );
            $productLock->execute(['product_id' => $productId]);
            if ($productLock->fetchColumn() === false) {
                $pdo->rollBack();
                $this->renderIndex('Sản phẩm được chọn không còn tồn tại.', $values);
                return;
            }

            $serialInsert = $pdo->prepare(
                'INSERT INTO product_serials '
                . '(product_id, serial_number, status, warranty_months) '
                . 'VALUES (:product_id, :serial_number, :status, :warranty_months)'
            );
            foreach ($serialNumbers as $serialNumber) {
                $serialInsert->execute([
                    'product_id' => $productId,
                    'serial_number' => $serialNumber,
                    'status' => 'in_stock',
                    'warranty_months' => $warrantyMonths,
                ]);
            }

            $stockUpdate = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty + :quantity WHERE id = :product_id'
            );
            $stockUpdate->execute([
                'quantity' => count($serialNumbers),
                'product_id' => $productId,
            ]);
            if ($stockUpdate->rowCount() !== 1) {
                throw new RuntimeException('Không thể cập nhật số lượng tồn kho.');
            }

            (new ActivityLog())->log(
                'serial.create',
                sprintf(
                    'Thêm %d Serial/IMEI cho sản phẩm #%d vào kho.',
                    count($serialNumbers),
                    $productId
                )
            );
            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (
                $exception->getCode() === '23000'
                && isset($exception->errorInfo[1])
                && (int) $exception->errorInfo[1] === 1062
            ) {
                $this->renderIndex('Một hoặc nhiều mã Serial/IMEI đã tồn tại.', $values);
                return;
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $_SESSION['warranty_success'] = sprintf(
            'Đã thêm %d mã Serial/IMEI vào kho.',
            count($serialNumbers)
        );
        $this->redirect('/warranty/index');
    }

    public function search(): void
    {
        $serialNumber = $_GET['serial_number'] ?? '';
        $serialNumber = is_string($serialNumber) ? trim($serialNumber) : '';
        $searched = $serialNumber !== '';
        $error = null;
        $serial = null;

        if ($searched) {
            if (preg_match('/\A[^\r\n\0]{1,100}\z/u', $serialNumber) !== 1) {
                $error = 'Số Serial/IMEI không hợp lệ.';
            } else {
                $serial = (new ProductSerial())->findBySerial($serialNumber);
                if ($serial === null) {
                    $error = 'Không tìm thấy thông tin bảo hành cho Serial/IMEI này.';
                }
            }
        }

        $this->render('inventory/warranties/search', [
            'error' => $error,
            'pageTitle' => 'Tra cứu bảo hành - Mini ERP',
            'searched' => $searched,
            'serial' => $serial,
            'serialNumber' => $serialNumber,
        ]);
    }

    private function renderIndex(
        ?string $error = null,
        ?array $formValues = null,
        ?string $success = null,
        int $page = 1
    ): void {
        $canManage = AuthMiddleware::hasPermission('inventory.manage_warranty');
        $result = (new ProductSerial())->getPaginatedWithProduct($page, self::ITEMS_PER_PAGE);
        $this->render('inventory/warranties/index', [
            'addSerialValues' => $formValues ?? [
                'product_id' => '',
                'serial_numbers' => '',
                'warranty_months' => '12',
            ],
            'canManage' => $canManage,
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'openAddSerialModal' => $formValues !== null,
            'pageTitle' => 'Bảo hành & Serial/IMEI - Mini ERP',
            'pagination' => $result['pagination'],
            'products' => $canManage ? (new Product())->findAll() : [],
            'serials' => $result['items'],
            'success' => $success,
        ]);
    }

    private function validateValues(array $values, ?array $serialNumbers): ?string
    {
        if (!preg_match('/\A[1-9][0-9]*\z/', $values['product_id'])) {
            return 'Vui lòng chọn sản phẩm.';
        }

        if ((new Product())->findById($values['product_id']) === null) {
            return 'Sản phẩm được chọn không tồn tại.';
        }

        if (
            !preg_match('/\A[1-9][0-9]{0,2}\z/', $values['warranty_months'])
            || (int) $values['warranty_months'] > self::MAX_WARRANTY_MONTHS
        ) {
            return 'Thời hạn bảo hành phải từ 1 đến 120 tháng.';
        }

        if (strlen($values['serial_numbers']) > self::MAX_SERIAL_TEXT_LENGTH) {
            return 'Danh sách Serial/IMEI quá dài.';
        }

        if ($serialNumbers === null) {
            return 'Danh sách Serial/IMEI không hợp lệ.';
        }

        if ($serialNumbers === []) {
            return 'Vui lòng nhập ít nhất một mã Serial/IMEI.';
        }

        if (count($serialNumbers) > self::MAX_SERIALS) {
            return 'Mỗi lần chỉ có thể thêm tối đa 1000 mã Serial/IMEI.';
        }

        $serialModel = new ProductSerial();
        $usedSerialNumbers = [];
        foreach ($serialNumbers as $serialNumber) {
            $key = bin2hex($serialNumber);
            if (
                preg_match('/\A[^\r\n\0]{1,100}\z/u', $serialNumber) !== 1
                || isset($usedSerialNumbers[$key])
            ) {
                return 'Danh sách chứa mã Serial/IMEI không hợp lệ hoặc bị trùng.';
            }

            if ($serialModel->findBySerial($serialNumber) !== null) {
                return 'Mã Serial/IMEI đã tồn tại trong hệ thống.';
            }

            $usedSerialNumbers[$key] = true;
        }

        return null;
    }

    private function parseSerialNumbers(string $serialText): ?array
    {
        if (strlen($serialText) > self::MAX_SERIAL_TEXT_LENGTH) {
            return null;
        }

        $lines = preg_split('/\R/u', $serialText);
        if ($lines === false) {
            return null;
        }

        return array_values(array_filter(
            array_map('trim', $lines),
            static fn (string $serialNumber): bool => $serialNumber !== ''
        ));
    }

    private function postString(string $field): string
    {
        $value = $_POST[$field] ?? '';

        return is_string($value) ? trim($value) : '';
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
}
