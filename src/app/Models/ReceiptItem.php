<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ReceiptItem extends Model
{
    protected string $table = 'inventory_receipt_items';

    public function getItemsByReceiptId(int|string $receiptId): array
    {
        $statement = $this->db->prepare(
            'SELECT inventory_receipt_items.*, '
            . 'products.name AS product_name, products.sku, products.unit '
            . 'FROM inventory_receipt_items '
            . 'INNER JOIN products ON products.id = inventory_receipt_items.product_id '
            . 'WHERE inventory_receipt_items.receipt_id = :receipt_id '
            . 'ORDER BY inventory_receipt_items.id ASC'
        );
        $statement->execute(['receipt_id' => $receiptId]);

        return $statement->fetchAll();
    }
}
