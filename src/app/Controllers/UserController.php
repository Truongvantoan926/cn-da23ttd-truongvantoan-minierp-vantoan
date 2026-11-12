<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use RuntimeException;

final class UserController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkPermission('auth.manage_users');
    }

    public function index(): void
    {
        $userModel = new User();
        $roles = $userModel->getAssignableRoles();
        if ($roles === []) {
            throw new RuntimeException('Không tìm thấy vai trò để quản lý tài khoản.');
        }

        $error = $_SESSION['user_management_error'] ?? null;
        $success = $_SESSION['user_management_success'] ?? null;
        unset($_SESSION['user_management_error'], $_SESSION['user_management_success']);

        $this->render('users/index', [
            'csrfToken' => $this->csrfToken(),
            'error' => is_string($error) ? $error : null,
            'roles' => $roles,
            'success' => is_string($success) ? $success : null,
            'users' => $userModel->getAccountsWithRoles(),
            'pageTitle' => 'Quản lý tài khoản - Mini ERP',
        ]);
    }

    public function update(string $id): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            $this->redirectWithError('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $roleId = filter_var(
            $_POST['role_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if ($userId === false || $roleId === false) {
            $this->redirectWithError('Tài khoản hoặc vai trò không hợp lệ.');
        }

        $userModel = new User();
        $account = $userModel->findAccountWithRoleById($userId);
        if ($account === null) {
            $this->redirectWithError('Không tìm thấy tài khoản cần cập nhật.');
        }

        $selectedRole = null;
        foreach ($userModel->getAssignableRoles() as $role) {
            if ((int) $role['id'] === $roleId) {
                $selectedRole = $role;
                break;
            }
        }
        if ($selectedRole === null) {
            $this->redirectWithError('Vai trò được chọn không tồn tại.');
        }

        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (
            $currentUserId !== false
            && $userId === $currentUserId
            && $roleId !== (int) $account['role_id']
        ) {
            $this->redirectWithError('Không thể thay đổi vai trò của tài khoản đang đăng nhập.');
        }

        $adminRoleId = null;
        foreach ($userModel->getAssignableRoles() as $role) {
            if ($role['name'] === 'admin') {
                $adminRoleId = (int) $role['id'];
                break;
            }
        }
        if (
            $adminRoleId !== null
            && (int) $account['role_id'] === $adminRoleId
            && $roleId !== $adminRoleId
            && $userModel->countUsersWithRoleId($adminRoleId) <= 1
        ) {
            $this->redirectWithError('Không thể hạ quyền quản trị viên cuối cùng.');
        }

        if ($roleId === (int) $account['role_id']) {
            $_SESSION['user_management_success'] = 'Vai trò của tài khoản không thay đổi.';
            $this->redirect('/user/index');
        }

        if ($userModel->updateRole($userId, $roleId) !== 1) {
            $this->redirectWithError('Không thể cập nhật vai trò. Vui lòng tải lại trang.');
        }

        (new ActivityLog())->log(
            'user.role_updated',
            sprintf(
                'Cập nhật vai trò tài khoản %s từ %s thành %s.',
                (string) $account['username'],
                (string) $account['role_name'],
                (string) $selectedRole['name']
            )
        );

        $_SESSION['user_management_success'] = 'Đã cập nhật vai trò tài khoản.';
        $this->redirect('/user/index');
    }

    public function delete(string $id): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            $this->redirectWithError('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.');
        }

        $userId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($userId === false) {
            $this->redirectWithError('Tài khoản cần xóa không hợp lệ.');
        }

        $userModel = new User();
        $account = $userModel->findAccountWithRoleById($userId);
        if ($account === null) {
            $this->redirectWithError('Không tìm thấy tài khoản cần xóa.');
        }

        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($currentUserId !== false && $userId === $currentUserId) {
            $this->redirectWithError('Không thể xóa tài khoản đang đăng nhập.');
        }

        foreach ($userModel->getAssignableRoles() as $role) {
            if (
                $role['name'] === 'admin'
                && (int) $account['role_id'] === (int) $role['id']
                && $userModel->countUsersWithRoleId((int) $role['id']) <= 1
            ) {
                $this->redirectWithError('Không thể xóa quản trị viên cuối cùng.');
            }
        }

        if ($userModel->deleteAccount($userId) !== 1) {
            $this->redirectWithError('Không thể xóa tài khoản. Vui lòng tải lại trang.');
        }

        (new ActivityLog())->log(
            'user.deleted',
            sprintf('Xóa tài khoản %s.', (string) $account['username'])
        );

        $_SESSION['user_management_success'] = 'Đã xóa tài khoản.';
        $this->redirect('/user/index');
    }

    private function redirectWithError(string $message): void
    {
        $_SESSION['user_management_error'] = $message;
        $this->redirect('/user/index');
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
