<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Customer;
use PDOException;

final class CustomerController extends Controller
{
    private const TYPES = ['individual', 'business'];

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
            (new Customer())->insert($this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('create', $values, 'Mã khách hàng đã tồn tại.');
            return;
        }

        $this->redirect('/customer/index');
    }

    public function edit(string $id): void
    {
        $customer = (new Customer())->findById($id);
        if ($customer === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', $this->formValues($customer), null, $customer);
    }

    public function update(string $id): void
    {
        $model = new Customer();
        $customer = $model->findById($id);
        if ($customer === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                $this->formValues($customer),
                'Phương thức gửi yêu cầu không hợp lệ.',
                $customer
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $customer);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $customer);
            return;
        }

        try {
            $model->update($id, $this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm('edit', $values, 'Mã khách hàng đã tồn tại.', $customer);
            return;
        }

        $this->redirect('/customer/index');
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
            (new Customer())->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderIndex('Không thể xóa khách hàng vì đã có cơ hội hoặc đơn hàng liên quan.');
            return;
        }

        $this->redirect('/customer/index');
    }

    private function renderIndex(?string $error = null): void
    {
        $this->render('crm/customers/index', [
            'csrfToken' => $this->csrfToken(),
            'customers' => (new Customer())->findAll(),
            'error' => $error,
            'pageTitle' => 'Khách hàng - Mini ERP',
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $customer = null
    ): void {
        $this->render('crm/customers/' . $form, [
            'csrfToken' => $this->csrfToken(),
            'customer' => $customer,
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' khách hàng - Mini ERP',
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
            'type' => 'business',
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

    private function formValues(array $customer): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $customer[$field] ?? '';
            $values[$field] = $value === null ? '' : (string) $value;
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,29}\z/', $values['code'])) {
            return 'Mã khách hàng phải có từ 1 đến 30 ký tự chữ, số, gạch ngang hoặc gạch dưới.';
        }

        if (!$this->hasTextLength($values['name'], 180)) {
            return 'Tên khách hàng là bắt buộc và không được vượt quá 180 ký tự.';
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

        if (!in_array($values['type'], self::TYPES, true)) {
            return 'Phân loại khách hàng không hợp lệ.';
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
            'type' => $values['type'],
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
        $this->renderIndex('Không tìm thấy khách hàng được yêu cầu.');
    }
}
