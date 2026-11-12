<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use PDOException;
use RuntimeException;

final class AuthController extends Controller
{
    private ?User $userModel = null;

    public function index(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard/index');
        }

        $error = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        unset($_SESSION['auth_error']);
        unset($_SESSION['auth_success']);
        $this->showLoginForm(
            is_string($error) ? $error : null,
            is_string($success) ? $success : null
        );
    }

    public function register(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard/index');
        }

        $values = [
            'username' => trim($this->postString('username')),
            'full_name' => trim($this->postString('full_name')),
            'phone' => trim($this->postString('phone')),
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
                header('Allow: GET, POST');
                http_response_code(405);
                $this->showRegisterForm('Phương thức gửi yêu cầu không hợp lệ.');
                return;
            }

            $this->showRegisterForm();
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            $this->showRegisterForm('Phiên đăng ký đã hết hạn. Vui lòng thử lại.', $values);
            return;
        }

        $password = $this->postString('password');
        $error = $this->validateRegistration($values, $password);
        if ($error !== null) {
            $this->showRegisterForm($error, $values);
            return;
        }

        $this->userModel = $this->userModel ?? new User();
        if ($this->userModel->findByUsername($values['username']) !== false) {
            $this->showRegisterForm('Tên đăng nhập đã được sử dụng.', $values);
            return;
        }

        $roleId = $this->userModel->findLowestPrivilegeRoleId();
        if ($roleId === null) {
            throw new RuntimeException('Không tìm thấy vai trò để tạo tài khoản.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $this->userModel->createAccount(
                $values['username'],
                $passwordHash,
                $values['full_name'],
                $values['phone'] !== '' ? $values['phone'] : null,
                $roleId
            );
        } catch (PDOException $exception) {
            if (
                $exception->getCode() === '23000'
                && isset($exception->errorInfo[1])
                && (int) $exception->errorInfo[1] === 1062
            ) {
                $this->showRegisterForm('Tên đăng nhập đã được sử dụng.', $values);
                return;
            }

            throw $exception;
        }

        $_SESSION['auth_success'] = 'Đăng ký thành công. Vui lòng đăng nhập.';
        $this->redirect('/auth/index');
    }

    public function login(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            $this->showLoginForm('Phương thức gửi yêu cầu không hợp lệ.');
            return;
        }

        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $submittedToken = $_POST['csrf_token'] ?? null;
        unset($_SESSION['csrf_token']);

        if (
            !is_string($sessionToken)
            || !is_string($submittedToken)
            || $sessionToken === ''
            || !hash_equals($sessionToken, $submittedToken)
        ) {
            $this->showLoginForm('Phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
            return;
        }

        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (
            !is_string($username)
            || !is_string($password)
            || trim($username) === ''
            || $password === ''
        ) {
            $this->showLoginForm('Vui lòng nhập tên đăng nhập và mật khẩu.');
            return;
        }

        $username = trim($username);
        $this->userModel = $this->userModel ?? new User();
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            $_SESSION['auth_error'] = 'Tài khoản không tồn tại';
            $this->redirect('/auth/index');
        }

        $passwordHash = $user['password_hash'] ?? null;
        if (!is_string($passwordHash) || !password_verify($password, $passwordHash)) {
            $_SESSION['auth_error'] = 'Mật khẩu không chính xác';
            $this->redirect('/auth/index');
        }

        if (!isset($user['id'])) {
            throw new \RuntimeException('Bản ghi tài khoản không có ID.');
        }

        $roleId = isset($user['role_id']) ? (int) $user['role_id'] : null;
        $roleLevel = $roleId === null
            ? null
            : $this->userModel->findRoleLevelById($roleId);
        if ($roleLevel === null) {
            throw new \RuntimeException('Không tìm thấy vai trò của tài khoản.');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = (string) ($user['username'] ?? $username);
        $_SESSION['role_id'] = $roleId;
        $_SESSION['role_level'] = $roleLevel;
        unset($_SESSION['csrf_token'], $_SESSION['auth_error']);

        (new ActivityLog())->log('auth.login', 'Đăng nhập hệ thống thành công.');
        $this->redirect('/dashboard/index');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            (new ActivityLog())->log('auth.logout', 'Đăng xuất khỏi hệ thống.');
        }

        session_unset();

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->redirect('/auth/index');
    }

    private function showLoginForm(?string $error = null, ?string $success = null): void
    {
        $this->render('auth/login', [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'success' => $success,
            'pageTitle' => 'Đăng nhập - Mini ERP',
        ]);
    }

    private function showRegisterForm(?string $error = null, array $values = []): void
    {
        $this->render('auth/register', [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'values' => $values + ['username' => '', 'full_name' => '', 'phone' => ''],
            'pageTitle' => 'Đăng ký - Mini ERP',
        ]);
    }

    private function validateRegistration(array $values, string $password): ?string
    {
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9_.-]{2,79}\z/', $values['username'])) {
            return 'Tên đăng nhập phải có 3–80 ký tự, bắt đầu bằng chữ hoặc số.';
        }

        if (preg_match('/\A[^\r\n]{1,150}\z/u', $values['full_name']) !== 1) {
            return 'Họ tên là bắt buộc và không được vượt quá 150 ký tự.';
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            return 'Mật khẩu phải có từ 8 đến 72 ký tự.';
        }

        if (
            $values['phone'] !== ''
            && preg_match('/\A[0-9+().\s-]{1,30}\z/u', $values['phone']) !== 1
        ) {
            return 'Số điện thoại không hợp lệ.';
        }

        return null;
    }

    private function postString(string $field): string
    {
        $value = $_POST[$field] ?? '';

        return is_string($value) ? $value : '';
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
