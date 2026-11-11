<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    private ?User $userModel = null;

    public function index(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard/index');
        }

        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);
        $this->showLoginForm(is_string($error) ? $error : null);
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

        $this->redirect('/dashboard/index');
    }

    public function logout(): void
    {
        $_SESSION = [];

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

    private function showLoginForm(?string $error = null): void
    {
        $token = $_SESSION['csrf_token'] ?? null;
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        $this->render('auth/login', [
            'csrfToken' => $token,
            'error' => $error,
            'pageTitle' => 'Đăng nhập - Mini ERP',
        ]);
    }
}
