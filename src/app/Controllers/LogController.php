<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\ActivityLog;

final class LogController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkRole([1, 2]);
    }

    public function index(): void
    {
        $this->render('logs/index', [
            'logs' => (new ActivityLog())->getRecent(50),
            'pageTitle' => 'Nhật ký hệ thống - Mini ERP',
        ]);
    }
}
