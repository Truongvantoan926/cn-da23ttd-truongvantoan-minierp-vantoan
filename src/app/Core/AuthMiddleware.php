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
}
