<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;
use PDO;
use PDOStatement;

final class Transaction extends Model
{
    protected string $table = 'transactions';

    public function getSummary(array $filters = []): array
    {
        [$where, $parameters] = $this->filterConditions($filters);
        $statement = $this->db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
                COALESCE(SUM(CASE
                    WHEN type = 'income' THEN amount
                    WHEN type = 'expense' THEN -amount
                    ELSE 0
                END), 0) AS balance
             FROM transactions"
            . $where
        );
        $this->executeWithParameters($statement, $parameters);

        return $statement->fetch();
    }

    public function getAllWithUser(array $filters = []): array
    {
        [$where, $parameters] = $this->filterConditions($filters);
        $statement = $this->db->prepare(
            'SELECT transactions.*, '
            . 'COALESCE(NULLIF(employees.full_name, \'\'), users.username) AS created_by_name '
            . 'FROM transactions '
            . 'LEFT JOIN users ON users.id = transactions.created_by '
            . 'LEFT JOIN employees ON employees.id = users.employee_id '
            . $where
            . ' ORDER BY transactions.trans_date DESC, transactions.id DESC'
        );
        $this->executeWithParameters($statement, $parameters);

        return $statement->fetchAll();
    }

    public function getPaginatedWithUser(int $page, int $perPage, array $filters = []): array
    {
        $from = ' FROM transactions '
            . 'LEFT JOIN users ON users.id = transactions.created_by '
            . 'LEFT JOIN employees ON employees.id = users.employee_id';
        [$where, $parameters] = $this->filterConditions($filters);

        return $this->paginate(
            'SELECT transactions.*, '
                . 'COALESCE(NULLIF(employees.full_name, \'\'), users.username) AS created_by_name'
                . $from
                . $where
                . ' ORDER BY transactions.trans_date DESC, transactions.id DESC',
            'SELECT COUNT(*)' . $from . $where,
            $parameters,
            $page,
            $perPage
        );
    }

    public function getDailyTotals(array $filters = []): array
    {
        [$where, $parameters] = $this->filterConditions($filters);
        $statement = $this->db->prepare(
            "SELECT trans_date,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense
             FROM transactions"
            . $where
            . ' GROUP BY trans_date ORDER BY trans_date DESC'
        );
        $this->executeWithParameters($statement, $parameters);

        $totals = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $totals[(string) $row['trans_date']] = $row;
        }

        return $totals;
    }

    public function getAvailableYears(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT YEAR(trans_date) AS transaction_year '
            . 'FROM transactions ORDER BY transaction_year DESC'
        );
        $years = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
        $currentYear = (int) date('Y');
        if (!in_array($currentYear, $years, true)) {
            $years[] = $currentYear;
            rsort($years, SORT_NUMERIC);
        }

        return $years;
    }

    private function filterConditions(array $filters): array
    {
        $conditions = [];
        $parameters = [];
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(transactions.code LIKE :filter_code '
                . 'OR transactions.category LIKE :filter_category '
                . 'OR COALESCE(NULLIF(transactions.description, \'\'), transactions.note, \'\') '
                . 'LIKE :filter_description)';
            $likeSearch = '%' . $search . '%';
            $parameters[':filter_code'] = $likeSearch;
            $parameters[':filter_category'] = $likeSearch;
            $parameters[':filter_description'] = $likeSearch;
        }

        $type = (string) ($filters['type'] ?? 'all');
        if ($type === 'in') {
            $conditions[] = 'transactions.type = :filter_type';
            $parameters[':filter_type'] = 'income';
        } elseif ($type === 'out') {
            $conditions[] = 'transactions.type = :filter_type';
            $parameters[':filter_type'] = 'expense';
        } elseif ($type !== 'all') {
            throw new InvalidArgumentException('Bộ lọc loại giao dịch không hợp lệ.');
        }

        $date = (string) ($filters['date'] ?? '');
        if ($date !== '') {
            $conditions[] = 'transactions.trans_date = :filter_date';
            $parameters[':filter_date'] = $date;
        } else {
            $month = (string) ($filters['month'] ?? '');
            if ($month !== '') {
                $conditions[] = 'MONTH(transactions.trans_date) = :filter_month';
                $parameters[':filter_month'] = (int) $month;
            }

            $year = (string) ($filters['year'] ?? '');
            if ($year !== '') {
                $conditions[] = 'YEAR(transactions.trans_date) = :filter_year';
                $parameters[':filter_year'] = (int) $year;
            }
        }

        return [
            $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions),
            $parameters,
        ];
    }

    private function executeWithParameters(PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $placeholder => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue($placeholder, $value, $type);
        }

        $statement->execute();
    }
}
