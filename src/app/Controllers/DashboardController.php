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
        $productCount = (new Product())->countAll();
        $receiptModel = new Receipt();
        $receiptCount = $receiptModel->countAll();
        $financeSummary = (new Transaction())->getSummary();

        $this->render('dashboard/index', [
            'employeeCount' => $employeeCount,
            'productCount' => $productCount,
            'receiptCount' => $receiptCount,
            'financeSummary' => $financeSummary,
            'recentReceipts' => $receiptModel->getRecent(),
            'username' => $username,
            'pageTitle' => 'Dashboard - Mini ERP',
        ]);
    }
}
