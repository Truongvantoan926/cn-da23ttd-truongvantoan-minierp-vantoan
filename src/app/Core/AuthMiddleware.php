<?php

declare(strict_types=1);

namespace App\Core;

final class AuthMiddleware
{
    private const PERMISSIONS_BY_ROLE = [
        'admin' => ['*'],
        'manager' => [
            'audit.view',
            'crm.view',
            'dashboard.view',
            'finance.view',
            'hr.view',
            'inventory.view',
        ],
        'accountant' => [
            'dashboard.view',
            'finance.manage',
            'finance.view',
            'reports.view',
        ],
        'warehouse' => [
            'dashboard.view',
            'inventory.manage_categories',
            'inventory.manage_products',
            'inventory.manage_receipts',
            'inventory.manage_stock',
            'inventory.manage_warranty',
            'inventory.view',
        ],
        'hr' => [
            'dashboard.view',
            'hr.manage',
            'hr.manage_payroll',
            'hr.view',
        ],
        'sales' => [
            'crm.manage_customers',
            'crm.manage_orders',
            'crm.manage_suppliers',
            'crm.view',
            'dashboard.view',
        ],
    ];

    private static array $roleNameCache = [];

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

    public static function hasPermission(string $permission): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $roleId = filter_var($_SESSION['role_id'] ?? null, FILTER_VALIDATE_INT);
        if (!is_int($roleId) || $roleId < 1 || $permission === '') {
            return false;
        }

        $roleName = self::roleName($roleId);
        $permissions = self::PERMISSIONS_BY_ROLE[$roleName] ?? [];

        return in_array('*', $permissions, true)
            || in_array($permission, $permissions, true);
    }

    public static function checkPermission(string $permission): void
    {
        self::check();
        if (self::hasPermission($permission)) {
            return;
        }

        $_SESSION['flash_error'] = 'Bạn không có quyền truy cập chức năng này.';
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if (self::hasPermission('dashboard.view')) {
            header('Location: ' . $baseUrl . '/dashboard/index', true, 302);
            exit;
        }

        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="vi"><meta charset="utf-8">'
            . '<title>403 - Từ chối truy cập</title>'
            . '<h1>403 - Từ chối truy cập</h1>'
            . '<p>Bạn không có quyền truy cập chức năng này.</p></html>';
        exit;
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

    private static function roleName(int $roleId): string
    {
        if (!array_key_exists($roleId, self::$roleNameCache)) {
            $statement = Database::getInstance()->getConnection()->prepare(
                'SELECT name FROM roles WHERE id = :role_id LIMIT 1'
            );
            $statement->execute(['role_id' => $roleId]);
            $roleName = $statement->fetchColumn();
            self::$roleNameCache[$roleId] = is_string($roleName)
                ? strtolower(trim($roleName))
                : '';
        }

        return self::$roleNameCache[$roleId];
    }
}
