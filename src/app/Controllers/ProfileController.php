<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\User;
use RuntimeException;

final class ProfileController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $error = $_SESSION['profile_error'] ?? null;
        $success = $_SESSION['profile_success'] ?? null;
        unset($_SESSION['profile_error'], $_SESSION['profile_success']);

        $this->renderProfile(
            is_string($error) ? $error : null,
            is_string($success) ? $success : null
        );
    }

    public function update(): void
    {
        $profile = $this->requireProfile();
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderProfile('Phương thức gửi yêu cầu không hợp lệ.', null, $profile);
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderProfile('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', null, $profile);
            return;
        }

        $fullName = $this->postString('full_name');
        $phone = $this->postString('phone');
        $profileValues = ['full_name' => trim($fullName), 'phone' => trim($phone)];
        $error = $this->validateProfile($profileValues);
        if ($error !== null) {
            $this->renderProfile($error, null, $profile, $profileValues);
            return;
        }

        $this->userModel->updateProfile(
            (int) $_SESSION['user_id'],
            $profileValues['full_name'],
            $profileValues['phone'] !== '' ? $profileValues['phone'] : null
        );
        $_SESSION['profile_success'] = 'Cập nhật thông tin cá nhân thành công.';
        $this->redirect('/profile/index');
    }

    public function changePassword(): void
    {
        $profile = $this->requireProfile();
        if (!$this->isPostRequest()) {
            http_response_code(405);
            header('Allow: POST');
            $this->renderProfile('Phương thức gửi yêu cầu không hợp lệ.', null, $profile);
            return;
        }

        if (!$this->hasValidCsrfToken()) {
            http_response_code(403);
            $this->renderProfile('Yêu cầu không hợp lệ hoặc phiên đã hết hạn.', null, $profile);
            return;
        }

        $currentPassword = $this->postString('current_password');
        $newPassword = $this->postString('new_password');
        $confirmPassword = $this->postString('confirm_password');
        $passwordHash = $this->userModel->findPasswordHashById((int) $_SESSION['user_id']);

        if ($passwordHash === null || !password_verify($currentPassword, $passwordHash)) {
            $this->renderProfile('Mật khẩu hiện tại không chính xác.', null, $profile);
            return;
        }

        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) {
            $this->renderProfile('Mật khẩu mới phải có độ dài từ 8 đến 72 byte.', null, $profile);
            return;
        }

        if (!hash_equals($newPassword, $confirmPassword)) {
            $this->renderProfile('Mật khẩu xác nhận không khớp.', null, $profile);
            return;
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!is_string($newPasswordHash)) {
            throw new RuntimeException('Không thể mã hóa mật khẩu mới.');
        }

        $this->userModel->updatePasswordHash(
            (int) $_SESSION['user_id'],
            $newPasswordHash
        );
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['profile_success'] = 'Đổi mật khẩu thành công.';
        $this->redirect('/profile/index');
    }

    private function renderProfile(
        ?string $error = null,
        ?string $success = null,
        ?array $profile = null,
        ?array $profileValues = null
    ): void {
        $profile = $profile ?? $this->requireProfile();
        $profileValues = $profileValues ?? [
            'full_name' => (string) $profile['full_name'],
            'phone' => (string) $profile['phone'],
        ];

        $this->render('profile/index', [
            'csrfToken' => $this->csrfToken(),
            'error' => $error,
            'pageTitle' => 'Hồ sơ cá nhân - Mini ERP',
            'profile' => $profile,
            'profileValues' => $profileValues,
            'success' => $success,
        ]);
    }

    private function requireProfile(): array
    {
        $profile = $this->userModel->getProfileById((int) $_SESSION['user_id']);
        if ($profile === null) {
            throw new RuntimeException('Không tìm thấy hồ sơ của tài khoản đang đăng nhập.');
        }

        return $profile;
    }

    private function validateProfile(array $values): ?string
    {
        if (preg_match('/\A[^\r\n]{1,150}\z/u', $values['full_name']) !== 1) {
            return 'Họ tên là bắt buộc và không được vượt quá 150 ký tự.';
        }

        if (
            $values['phone'] !== ''
            && preg_match('/\A[0-9+().\s-]{1,30}\z/u', $values['phone']) !== 1
        ) {
            return 'Số điện thoại chỉ được chứa chữ số và các ký tự + ( ) . - hoặc khoảng trắng.';
        }

        return null;
    }

    private function postString(string $field): string
    {
        $value = $_POST[$field] ?? '';

        return is_string($value) ? $value : '';
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
}
