<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Department;
use App\Models\Employee;
use DateTimeImmutable;
use PDOException;

final class EmployeeController extends Controller
{
    private const STATUSES = ['active', 'inactive', 'on_leave'];

    public function __construct()
    {
        AuthMiddleware::checkPermission('hr.view');
    }

    public function index(): void
    {
        $this->renderIndex(null, $this->requestedPage());
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $this->renderForm('create', $this->emptyValues());
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('hr.manage');
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
            (new Employee())->insert($this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm(
                'create',
                $values,
                'Mã nhân viên hoặc email đã tồn tại, hoặc phòng ban không còn hợp lệ.'
            );
            return;
        }

        $this->redirect('/employee/index');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $employee = (new Employee())->findById($id);
        if ($employee === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', $this->formValues($employee), null, $employee);
    }

    public function update(string $id): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $model = new Employee();
        $employee = $model->findById($id);
        if ($employee === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                $this->formValues($employee),
                'Phương thức gửi yêu cầu không hợp lệ.',
                $employee
            );
            return;
        }

        $values = $this->postedFields();
        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $values, 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $employee);
            return;
        }

        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $employee);
            return;
        }

        try {
            $model->update($id, $this->databaseValues($values));
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderForm(
                'edit',
                $values,
                'Mã nhân viên hoặc email đã tồn tại, hoặc phòng ban không còn hợp lệ.',
                $employee
            );
            return;
        }

        $this->redirect('/employee/index');
    }

    public function delete(string $id): void
    {
        AuthMiddleware::checkPermission('hr.manage');
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
            (new Employee())->delete($id);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->renderIndex('Không thể xóa nhân viên vì đã có dữ liệu liên quan.');
            return;
        }

        $this->redirect('/employee/index');
    }

    private function renderIndex(?string $error = null, int $page = 1): void
    {
        $result = (new Employee())->getPaginatedWithDepartment($page, self::ITEMS_PER_PAGE);
        $this->render('hr/employees/index', [
            'csrfToken' => $this->csrfToken(),
            'employees' => $result['items'],
            'error' => $error,
            'pageTitle' => 'Nhân viên - Mini ERP',
            'pagination' => $result['pagination'],
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $employee = null
    ): void {
        $this->render('hr/employees/' . $form, [
            'csrfToken' => $this->csrfToken(),
            'departments' => (new Department())->findAll(),
            'employee' => $employee,
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' nhân viên - Mini ERP',
            'values' => $values,
        ]);
    }

    private function emptyValues(): array
    {
        return [
            'code' => '',
            'full_name' => '',
            'dob' => '',
            'phone' => '',
            'email' => '',
            'department_id' => '',
            'position' => '',
            'base_salary' => '',
            'hire_date' => '',
            'status' => 'active',
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

    private function formValues(array $employee): array
    {
        $values = [];
        foreach (array_keys($this->emptyValues()) as $field) {
            $value = $employee[$field] ?? '';
            $values[$field] = $value === null ? '' : (string) $value;
        }

        return $values;
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,29}\z/', $values['code'])) {
            return 'Mã nhân viên phải có 1–30 ký tự, bắt đầu bằng chữ hoặc số.';
        }

        if (!$this->hasTextLength($values['full_name'], 150)) {
            return 'Họ tên là bắt buộc và không được vượt quá 150 ký tự.';
        }

        if ($values['dob'] !== '' && !$this->isValidDate($values['dob'])) {
            return 'Ngày sinh không hợp lệ.';
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

        if ($values['department_id'] !== '') {
            if (!preg_match('/\A[1-9][0-9]*\z/', $values['department_id'])) {
                return 'Phòng ban được chọn không hợp lệ.';
            }

            if ((new Department())->findById($values['department_id']) === null) {
                return 'Phòng ban được chọn không còn tồn tại.';
            }
        }

        if (!$this->hasOptionalTextLength($values['position'], 100)) {
            return 'Chức vụ không được vượt quá 100 ký tự.';
        }

        if (!preg_match('/\A[0-9]{1,13}(?:\.[0-9]{1,2})?\z/', $values['base_salary'])) {
            return 'Lương cơ bản phải là số không âm, tối đa 2 chữ số thập phân.';
        }

        if ($values['hire_date'] !== '' && !$this->isValidDate($values['hire_date'])) {
            return 'Ngày vào làm không hợp lệ.';
        }

        if (!in_array($values['status'], self::STATUSES, true)) {
            return 'Trạng thái nhân viên không hợp lệ.';
        }

        return null;
    }

    private function databaseValues(array $values): array
    {
        return [
            'code' => $values['code'],
            'full_name' => $values['full_name'],
            'dob' => $values['dob'] !== '' ? $values['dob'] : null,
            'phone' => $values['phone'] !== '' ? $values['phone'] : null,
            'email' => $values['email'] !== '' ? $values['email'] : null,
            'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
            'position' => $values['position'] !== '' ? $values['position'] : null,
            'base_salary' => $values['base_salary'],
            'hire_date' => $values['hire_date'] !== '' ? $values['hire_date'] : null,
            'status' => $values['status'],
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

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $date !== false
            && $date->format('Y-m-d') === $value
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0));
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
        $this->renderIndex('Không tìm thấy nhân viên được yêu cầu.');
    }
}
