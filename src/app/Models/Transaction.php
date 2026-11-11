<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Transaction extends Model
{
    protected string $table = 'transactions';

    public function getSummary(): array
    {
        $statement = $this->db->query(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
                COALESCE(SUM(CASE
                    WHEN type = 'income' THEN amount
                    WHEN type = 'expense' THEN -amount
                    ELSE 0
                END), 0) AS balance
             FROM transactions"
        );

        return $statement->fetch();
    }

    public function getAllWithUser(): array
    {
        $statement = $this->db->query(
            'SELECT transactions.*, '
            . 'COALESCE(NULLIF(employees.full_name, \'\'), users.username) AS created_by_name '
            . 'FROM transactions '
            . 'LEFT JOIN users ON users.id = transactions.created_by '
            . 'LEFT JOIN employees ON employees.id = users.employee_id '
            . 'ORDER BY transactions.trans_date DESC, transactions.id DESC'
        );

        return $statement->fetchAll();
    }
}
