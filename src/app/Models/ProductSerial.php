<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductSerial extends Model
{
    protected string $table = 'product_serials';

    public function getAllWithProduct(): array
    {
        $statement = $this->db->query(
            'SELECT product_serials.*, '
            . 'products.sku, products.name AS product_name, '
            . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH) '
            . 'AS warranty_end_date '
            . 'FROM product_serials '
            . 'INNER JOIN products ON products.id = product_serials.product_id '
            . 'ORDER BY products.name ASC, product_serials.serial_number ASC'
        );

        return $statement->fetchAll();
    }

    public function getPaginatedWithProduct(int $page, int $perPage): array
    {
        $from = ' FROM product_serials '
            . 'INNER JOIN products ON products.id = product_serials.product_id';

        return $this->paginate(
            'SELECT product_serials.*, products.sku, products.name AS product_name, '
                . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH) '
                . 'AS warranty_end_date'
                . $from
                . ' ORDER BY products.name ASC, product_serials.serial_number ASC',
            'SELECT COUNT(*)' . $from,
            [],
            $page,
            $perPage
        );
    }

    public function getByProductId(int|string $productId): array
    {
        $statement = $this->db->prepare(
            'SELECT product_serials.*, '
            . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH) '
            . 'AS warranty_end_date '
            . 'FROM product_serials '
            . 'WHERE product_serials.product_id = :product_id '
            . 'ORDER BY product_serials.serial_number ASC'
        );
        $statement->execute(['product_id' => $productId]);

        return $statement->fetchAll();
    }

    public function getPaginatedByProductId(
        int|string $productId,
        int $page,
        int $perPage
    ): array {
        $where = ' WHERE product_serials.product_id = :product_id';

        return $this->paginate(
            'SELECT product_serials.*, '
                . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH) '
                . 'AS warranty_end_date '
                . 'FROM product_serials'
                . $where
                . ' ORDER BY product_serials.serial_number ASC',
            'SELECT COUNT(*) FROM product_serials' . $where,
            [':product_id' => $productId],
            $page,
            $perPage
        );
    }

    public function getAllForReceiptForm(): array
    {
        $statement = $this->db->query(
            'SELECT product_id, serial_number, status '
            . 'FROM product_serials '
            . 'ORDER BY product_id ASC, serial_number ASC'
        );

        return $statement->fetchAll();
    }

    public function hasSerialsForProduct(int|string $productId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM product_serials WHERE product_id = :product_id LIMIT 1'
        );
        $statement->execute(['product_id' => $productId]);

        return $statement->fetchColumn() !== false;
    }

    public function createInStock(
        int $productId,
        string $serialNumber,
        ?int $receiptInId = null,
        int $warrantyMonths = 12
    ): string {
        return $this->insert([
            'product_id' => $productId,
            'serial_number' => $serialNumber,
            'receipt_in_id' => $receiptInId,
            'status' => 'in_stock',
            'warranty_months' => $warrantyMonths,
        ]);
    }

    public function findBySerial(string $serial_number): ?array
    {
        $statement = $this->db->prepare(
            'SELECT product_serials.*, '
            . 'products.sku, products.name AS product_name, '
            . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH) '
            . 'AS warranty_end_date, '
            . 'DATEDIFF('
            . 'DATE_ADD(product_serials.sold_date, INTERVAL product_serials.warranty_months MONTH), '
            . 'CURRENT_DATE'
            . ') AS warranty_days_remaining '
            . 'FROM product_serials '
            . 'INNER JOIN products ON products.id = product_serials.product_id '
            . 'WHERE product_serials.serial_number = :serial_number '
            . 'LIMIT 1'
        );
        $statement->execute(['serial_number' => $serial_number]);
        $serial = $statement->fetch();

        return $serial === false ? null : $serial;
    }
}
