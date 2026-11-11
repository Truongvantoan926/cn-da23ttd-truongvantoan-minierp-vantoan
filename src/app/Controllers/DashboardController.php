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
        AuthMiddleware::check();
    }

    public function index(): void
    {
        $username = $_SESSION['username'] ?? 'Người dùng';
        if (!is_string($username) || $username === '') {
            $username = 'Người dùng';
        }

        $employeeCount = (new Employee())->countAll();
        $productModel = new Product();
        $productCount = $productModel->countAll();
        $receiptModel = new Receipt();
        $receiptCount = $receiptModel->countAll();
        $financeSummary = (new Transaction())->getSummary();
        $lowStockProducts = $productModel->getLowStockProducts();
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        $this->render('dashboard/index', [
            'employeeCount' => $employeeCount,
            'productCount' => $productCount,
            'receiptCount' => $receiptCount,
            'financeSummary' => $financeSummary,
            'recentReceipts' => $receiptModel->getRecent(),
            'lowStockProducts' => $lowStockProducts,
            'flashError' => is_string($flashError) ? $flashError : null,
            'username' => $username,
            'pageTitle' => 'Dashboard - Mini ERP',
        ]);
    }
}
