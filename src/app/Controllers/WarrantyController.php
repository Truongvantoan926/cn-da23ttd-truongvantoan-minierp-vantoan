<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Controller;
use App\Models\ProductSerial;

final class WarrantyController extends Controller
{
    public function __construct()
    {
        AuthMiddleware::checkRole([1, 2, 3, 4, 6]);
    }

    public function index(): void
    {
        $this->render('inventory/warranties/index', [
            'pageTitle' => 'Bảo hành & Serial/IMEI - Mini ERP',
            'serials' => (new ProductSerial())->getAllWithProduct(),
        ]);
    }

    public function search(): void
    {
        $serialNumber = $_GET['serial_number'] ?? '';
        $serialNumber = is_string($serialNumber) ? trim($serialNumber) : '';
        $searched = $serialNumber !== '';
        $error = null;
        $serial = null;

        if ($searched) {
            if (preg_match('/\A[^\r\n\0]{1,100}\z/u', $serialNumber) !== 1) {
                $error = 'Số Serial/IMEI không hợp lệ.';
            } else {
                $serial = (new ProductSerial())->findBySerial($serialNumber);
                if ($serial === null) {
                    $error = 'Không tìm thấy thông tin bảo hành cho Serial/IMEI này.';
                }
            }
        }

        $this->render('inventory/warranties/search', [
            'error' => $error,
            'pageTitle' => 'Tra cứu bảo hành - Mini ERP',
            'searched' => $searched,
            'serial' => $serial,
            'serialNumber' => $serialNumber,
        ]);
    }
}
