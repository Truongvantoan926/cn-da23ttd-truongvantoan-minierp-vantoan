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
        AuthMiddleware::checkPermission('audit.view');
    }

    public function index(): void
    {
        $result = (new ActivityLog())->getPaginated($this->requestedPage(), self::ITEMS_PER_PAGE);
        $this->render('logs/index', [
            'logs' => $result['items'],
            'pagination' => $result['pagination'],
            'pageTitle' => 'Nhật ký hệ thống - Mini ERP',
        ]);
    }
}
