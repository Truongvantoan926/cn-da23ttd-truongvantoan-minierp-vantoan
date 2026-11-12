<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Transaction;

final class DashboardController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkPermission('dashboard.view');
    }

    public function index(): void
    {
        $username = $_SESSION['username'] ?? 'Người dùng';
        if (!is_string($username) || $username === '') {
            $username = 'Người dùng';
        }

        $canViewHr = AuthMiddleware::hasPermission('hr.view');
        $canViewInventory = AuthMiddleware::hasPermission('inventory.view');
        $canViewFinance = AuthMiddleware::hasPermission('finance.view');
        $employeeCount = $canViewHr ? (new Employee())->countAll() : 0;
        $productCount = 0;
        $receiptCount = 0;
        $recentReceipts = [];
        $lowStockProducts = [];
        if ($canViewInventory) {
            $productModel = new Product();
            $receiptModel = new Receipt();
            $productCount = $productModel->countAll();
            $receiptCount = $receiptModel->countAll();
            $recentReceipts = $receiptModel->getRecent();
            $lowStockProducts = $productModel->getLowStockProducts();
        }
        $financeSummary = $canViewFinance ? (new Transaction())->getSummary() : null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $this->render('dashboard/index', [
            'canViewFinance' => $canViewFinance,
            'canViewHr' => $canViewHr,
            'canViewInventory' => $canViewInventory,
            'employeeCount' => $employeeCount,
            'productCount' => $productCount,
            'receiptCount' => $receiptCount,
            'financeSummary' => $financeSummary,
            'recentReceipts' => $recentReceipts,
            'lowStockProducts' => $lowStockProducts,
            'flashError' => is_string($flashError) ? $flashError : null,
            'username' => $username,
            'pageTitle' => 'Dashboard - Mini ERP',
        ]);
    }
}
