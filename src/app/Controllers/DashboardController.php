<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;

final class DashboardController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::check();
    }

    public function index(): void
    {
        $username = $_SESSION['username'] ?? 'Người dùng';
        if (!is_string($username) || $username === '') {
            $username = 'Người dùng';
        }

        $this->render('dashboard/index', [
            'username' => $username,
            'pageTitle' => 'Dashboard - Mini ERP',
        ]);
    }
}
