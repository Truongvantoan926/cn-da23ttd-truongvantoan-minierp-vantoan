<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Receipt extends Model
{
    protected string $table = 'inventory_receipts';

    public function getAllWithDetails(): array
    {
        $statement = $this->db->query(
            $this->detailsSelect()
            . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC'
        );

        return $statement->fetchAll();
    }

    public function getPaginatedWithDetails(int $page, int $perPage): array
    {
        return $this->paginate(
            $this->detailsSelect()
                . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC',
            'SELECT COUNT(*) FROM inventory_receipts',
            [],
            $page,
            $perPage
        );
    }

    public function getRecent(): array
    {
        $statement = $this->db->query(
            $this->detailsSelect()
            . ' ORDER BY inventory_receipts.created_at DESC, inventory_receipts.id DESC LIMIT 5'
        );

        return $statement->fetchAll();
    }

    public function getByIdWithDetails(int|string $receiptId): ?array
    {
        $statement = $this->db->prepare(
            $this->detailsSelect() . ' WHERE inventory_receipts.id = :receipt_id LIMIT 1'
        );
        $statement->execute(['receipt_id' => $receiptId]);
        $receipt = $statement->fetch();

        return $receipt === false ? null : $receipt;
    }

    private function detailsSelect(): string
    {
        return 'SELECT inventory_receipts.*, '
            . 'COALESCE(suppliers.name, customers.name) AS partner_name, '
            . 'employees.full_name AS employee_name '
            . 'FROM inventory_receipts '
            . 'LEFT JOIN suppliers '
            . 'ON inventory_receipts.type = \'IN\' AND suppliers.id = inventory_receipts.partner_id '
            . 'LEFT JOIN customers '
            . 'ON inventory_receipts.type = \'OUT\' AND customers.id = inventory_receipts.partner_id '
            . 'LEFT JOIN employees ON employees.id = inventory_receipts.employee_id';
    }
}
