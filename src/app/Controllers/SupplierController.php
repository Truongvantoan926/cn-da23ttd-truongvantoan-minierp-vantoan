<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Supplier;
use PDOException;

final class SupplierController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkPermission('crm.view');
    }

    public function index(): void
    {
        $this->renderIndex(null, $this->requestedPage());
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('crm.manage_suppliers');
        $this->renderForm('create', $this->emptyValues());
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('crm.manage_suppliers');
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
            (new Supplier())->insert($this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('create', $values, 'Mã nhà cung cấp đã tồn tại.');
            return;
        }

        $this->redirect('/supplier/index');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::checkPermission('crm.manage_suppliers');
        $supplier = (new Supplier())->findById($id);
        if ($supplier === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', $this->formValues($supplier), null, $supplier);
    }

    public function update(string $id): void
    {
        AuthMiddleware::checkPermission('crm.manage_suppliers');
        $model = new Supplier();
        $supplier = $model->findById($id);
        if ($supplier === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                $this->formValues($supplier),
                'Phương thức gửi yêu cầu không hợp lệ.',
                $supplier
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $supplier);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $supplier);
            return;
        }

        try {
            $model->update($id, $this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('edit', $values, 'Mã nhà cung cấp đã tồn tại.', $supplier);
            return;
        }

        $this->redirect('/supplier/index');
    }

    public function delete(string $id): void
    {
        AuthMiddleware::checkPermission('crm.manage_suppliers');
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
            (new Supplier())->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderIndex('Không thể xóa nhà cung cấp vì đã có phiếu nhập liên quan.');
            return;
        }

        $this->redirect('/supplier/index');
    }

    private function renderIndex(?string $error = null, int $page = 1): void
    {
        $result = (new Supplier())->getPaginated($page, self::ITEMS_PER_PAGE);
        $this->render('crm/suppliers/index', [
            'canManage' => AuthMiddleware::hasRole(1)
                || AuthMiddleware::hasRole(2)
                || AuthMiddleware::hasRole(6),
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Nhà cung cấp - Mini ERP',
            'suppliers' => $result['items'],
            'pagination' => $result['pagination'],
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $supplier = null
    ): void {
        $this->render('crm/suppliers/' . $form, [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' nhà cung cấp - Mini ERP',
            'supplier' => $supplier,
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'code' => '',
            'name' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'tax_code' => '',
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

    private function formValues(array $supplier): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $supplier[$field] ?? '';
            $values[$field] = $value === null ? '' : (string) $value;
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,29}\z/', $values['code'])) {
            return 'Mã nhà cung cấp phải có từ 1 đến 30 ký tự chữ, số, gạch ngang hoặc gạch dưới.';
        }

        if (!$this->hasTextLength($values['name'], 180)) {
            return 'Tên nhà cung cấp là bắt buộc và không được vượt quá 180 ký tự.';
        }

        if ($values['phone'] !== '' && !preg_match('/\A[0-9+() .-]{1,30}\z/', $values['phone'])) {
            return 'Số điện thoại không hợp lệ.';
        }

        if (
            $values['email'] !== ''
            && (strlen($values['email']) > 190 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false)
        ) {
            return 'Email không hợp lệ.';
        }

        if (!$this->hasOptionalTextLength($values['address'], 255)) {
            return 'Địa chỉ không được vượt quá 255 ký tự.';
        }

        if (!$this->hasOptionalTextLength($values['tax_code'], 40)) {
            return 'Mã số thuế không được vượt quá 40 ký tự.';
        }

        return null;
    }

    private function databaseValues(array $values): array
    {
        return [
            'code' => $values['code'],
            'name' => $values['name'],
            'phone' => $values['phone'] !== '' ? $values['phone'] : null,
            'email' => $values['email'] !== '' ? $values['email'] : null,
            'address' => $values['address'] !== '' ? $values['address'] : null,
            'tax_code' => $values['tax_code'] !== '' ? $values['tax_code'] : null,
        ];
    }

    private function hasTextLength(string $value, int $maxLength): bool
    {
        return preg_match('/\A[^\r\n]{1,' . $maxLength . '}\z/u', $value) === 1;
    }

    private function hasOptionalTextLength(string $value, int $maxLength): bool
    {
        return $value === '' || $this->hasTextLength($value, $maxLength);
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
        $this->renderIndex('Không tìm thấy nhà cung cấp được yêu cầu.');
    }
}
