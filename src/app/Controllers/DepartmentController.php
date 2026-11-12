<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Department;
use App\Models\Employee;

final class DepartmentController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkPermission('hr.view');
    }

    public function index(): void
    {
        $this->renderIndex(null, $this->requestedPage());
    }

    public function show(string $id): void
    {
        $department = (new Department())->findById($id);
        if ($department === null) {
            $this->showNotFound();
            return;
        }

        $result = (new Employee())->getPaginatedByDepartmentIdWithRole(
            $id,
            $this->requestedPage(),
            self::ITEMS_PER_PAGE
        );
        $this->render('hr/departments/show', [
            'department' => $department,
            'employees' => $result['items'],
            'pageTitle' => (string) $department['name'] . ' - Mini ERP',
            'pagination' => $result['pagination'],
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $this->renderForm('create', ['code' => '', 'name' => '']);
    }

    public function store(): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm('create', ['code' => '', 'name' => ''], 'Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('create', $this->postedFields(), 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
            return;
        }

        $values = $this->postedFields();
        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('create', $values, $error);
            return;
        }

        (new Department())->insert($values);
        $this->redirect('/department/index');
    }

    public function edit(string $id): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $department = (new Department())->findById($id);
        if ($department === null) {
            $this->showNotFound();
            return;
        }

        $this->renderForm('edit', [
            'code' => (string) $department['code'],
            'name' => (string) $department['name'],
        ], null, $department);
    }

    public function update(string $id): void
    {
        AuthMiddleware::checkPermission('hr.manage');
        $model = new Department();
        $department = $model->findById($id);
        if ($department === null) {
            $this->showNotFound();
            return;
        }

        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderForm(
                'edit',
                ['code' => (string) $department['code'], 'name' => (string) $department['name']],
                'Phương thức gửi yêu cầu không hợp lệ.',
                $department
            );
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderForm('edit', $this->postedFields(), 'Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', $department);
            return;
        }

        $values = $this->postedFields();
        $error = $this->validateFields($values);
        if ($error !== null) {
            $this->renderForm('edit', $values, $error, $department);
            return;
        }

        $model->update($id, $values);
        $this->redirect('/department/index');
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

        (new Department())->delete($id);
        $this->redirect('/department/index');
    }

    private function renderIndex(?string $error = null, int $page = 1): void
    {
        $result = (new Department())->getPaginated($page, self::ITEMS_PER_PAGE);
        $this->render('hr/departments/index', [
            'departments' => $result['items'],
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Phòng ban - Mini ERP',
            'pagination' => $result['pagination'],
        ]);
    }

    private function renderForm(
        string $form,
        array $values,
        ?string $error = null,
        ?array $department = null
    ): void {
        $this->render('hr/departments/' . $form, [
            'csrfToken' => $this->csrfToken(),
            'department' => $department,
            'error' => $error,
            'pageTitle' => ($form === 'edit' ? 'Sửa' : 'Thêm') . ' phòng ban - Mini ERP',
            'values' => $values,
        ]);
    }

    private function postedFields(): array
    {
        $code = $_POST['code'] ?? '';
        $name = $_POST['name'] ?? '';

        return [
            'code' => is_string($code) ? trim($code) : '',
            'name' => is_string($name) ? trim($name) : '',
        ];
    }

    private function validateFields(array $values): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,29}\z/', $values['code'])) {
            return 'Mã phòng ban phải có 1–30 ký tự, bắt đầu bằng chữ hoặc số.';
        }

        if (!preg_match('/\A[^\r\n]{1,120}\z/u', $values['name'])) {
            return 'Tên phòng ban là bắt buộc và không được vượt quá 120 ký tự.';
        }

        return null;
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
        $this->renderIndex('Không tìm thấy phòng ban được yêu cầu.');
    }
}
