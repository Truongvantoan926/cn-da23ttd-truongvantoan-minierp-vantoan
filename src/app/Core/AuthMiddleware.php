<?php

declare(strict_types=1);

namespace App\Core;

final class AuthMiddleware
{
    public static function check(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
            header('Location: ' . $baseUrl . '/auth/index', true, 302);
            exit;
        }
    }

    public static function hasRole(int $roleId): bool
    {
        $sessionRoleId = filter_var($_SESSION['role_id'] ?? null, FILTER_VALIDATE_INT);

        return $roleId > 0 && $sessionRoleId === $roleId;
    }

    public static function checkRole(int|array $requiredRoles): void
    {
        self::check();
        $requiredRoles = is_array($requiredRoles) ? $requiredRoles : [$requiredRoles];

        foreach ($requiredRoles as $roleId) {
            if (is_int($roleId) && self::hasRole($roleId)) {
                return;
            }
        }

        $_SESSION['flash_error'] = 'Bạn không có quyền truy cập chức năng này';
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        header('Location: ' . $baseUrl . '/dashboard/index', true, 302);
        exit;
    }
}
