<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Category;
use App\Models\Product;
use PDOException;

final class ProductController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::check();
    }

    public function index(): void
    {
        $this->renderIndex();
    }

    public function create(): void
    {
        $this->renderForm('create', $this->emptyValues());
    }

    public function store(): void
    {
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm('create', $this->emptyValues(), 'Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('create', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('create', $values, $error);
            return;
        }

        try {
            (new Product())->insert($this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('create', $values, 'SKU đã tồn tại hoặc danh mục không còn hợp lệ.');
            return;
        }

        $this->redirect('/product/index');
    }

    public function edit(string $id): void
    {
        $product = (new Product())->findById($id);
        if ($product === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', $this->formValues($product), null, $product);
    }

    public function update(string $id): void
    {
        $model = new Product();
        $product = $model->findById($id);
        if ($product === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                $this->formValues($product),
                'Phương thức gửi yêu cầu không hợp lệ.',
                $product
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $product);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $product);
            return;
        }

        try {
            $model->update($id, $this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('edit', $values, 'SKU đã tồn tại hoặc danh mục không còn hợp lệ.', $product);
            return;
        }

        $this->redirect('/product/index');
    }

    public function delete(string $id): void
    {
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

        try {
            (new Product())->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderIndex('Không thể xóa sản phẩm vì đã có giao dịch kho hoặc đơn hàng liên quan.');
            return;
        }

        $this->redirect('/product/index');
    }

    private function renderIndex(?string $error = null): void
    {
        $this->render('inventory/products/index', [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Sản phẩm - Mini ERP',
            'products' => (new Product())->getAllWithCategory(),
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $product = null
    ): void {
        $this->render('inventory/products/' . $form, [
            'categories' => (new Category())->findAll(),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' sản phẩm - Mini ERP',
            'product' => $product,
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'sku' => '',
            'name' => '',
            'category_id' => '',
            'unit' => '',
            'cost_price' => '0.00',
            'sale_price' => '0.00',
            'min_stock' => '0.000',
        ];
    }

    private function postedFields(): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $_POST[$field] ?? '';
            $values[$field] = is_string($value) ? trim($value) : '';
        }

        return $values;
    }

    private function formValues(array $product): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $product[$field] ?? '';
            $values[$field] = $value === null ? '' : (string) $value;
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,59}\z/', $values['sku'])) {
            return 'SKU phải có từ 1 đến 60 ký tự chữ, số, dấu chấm, gạch ngang hoặc gạch dưới.';
        }

        if (!$this->hasTextLength($values['name'], 180)) {
            return 'Tên sản phẩm là bắt buộc và không được vượt quá 180 ký tự.';
        }

        if ($values['category_id'] !== '') {
            if (!preg_match('/\A[1-9][0-9]*\z/', $values['category_id'])) {
                return 'Danh mục được chọn không hợp lệ.';
            }

            if ((new Category())->findById($values['category_id']) === null) {
                return 'Danh mục được chọn không còn tồn tại.';
            }
        }

        if (!$this->hasTextLength($values['unit'], 30)) {
            return 'Đơn vị tính là bắt buộc và không được vượt quá 30 ký tự.';
        }

        if (!$this->isValidDecimal($values['cost_price'], 13, 2)) {
            return 'Giá vốn phải là số không âm, tối đa 2 chữ số thập phân.';
        }

        if (!$this->isValidDecimal($values['sale_price'], 13, 2)) {
            return 'Giá bán phải là số không âm, tối đa 2 chữ số thập phân.';
        }

        if ($this->compareDecimals($values['sale_price'], $values['cost_price'], 2) < 0) {
            return 'Giá bán phải lớn hơn hoặc bằng giá vốn.';
        }

        if (!$this->isValidDecimal($values['min_stock'], 12, 3)) {
            return 'Tồn kho tối thiểu phải là số không âm, tối đa 3 chữ số thập phân.';
        }

        return null;
    }

    private function databaseValues(array $values): array
    {
        return [
            'sku' => $values['sku'],
            'name' => $values['name'],
            'category_id' => $values['category_id'] !== '' ? (int) $values['category_id'] : null,
            'unit' => $values['unit'],
            'cost_price' => $values['cost_price'],
            'sale_price' => $values['sale_price'],
            'min_stock' => $values['min_stock'],
        ];
    }

    private function hasTextLength(string $value, int $maxLength): bool
    {
        return preg_match('/\A[^\r\n]{1,' . $maxLength . '}\z/u', $value) === 1;
    }

    private function isValidDecimal(string $value, int $maxIntegerDigits, int $scale): bool
    {
        return preg_match(
            '/\A[0-9]{1,' . $maxIntegerDigits . '}(?:\.[0-9]{1,' . $scale . '})?\z/',
            $value
        ) === 1;
    }

    private function compareDecimals(string $left, string $right, int $scale): int
    {
        $leftMinor = $this->toMinorUnits($left, $scale);
        $rightMinor = $this->toMinorUnits($right, $scale);

        if (strlen($leftMinor) !== strlen($rightMinor)) {
            return strlen($leftMinor) < strlen($rightMinor) ? -1 : 1;
        }

        return strcmp($leftMinor, $rightMinor);
    }

    private function toMinorUnits(string $value, int $scale): string
    {
        [$integer, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;
        $minorUnits = ltrim($integer . str_pad($fraction, $scale, '0'), '0');

        return $minorUnits === '' ? '0' : $minorUnits;
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
        $this->renderIndex('Không tìm thấy sản phẩm được yêu cầu.');
    }
}
