<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Employee extends Model
{
    protected string $table = 'employees';

    public function getAllWithDepartment(): array
    {
        $statement = $this->db->query(
            'SELECT employees.*, departments.name AS department_name '
            . 'FROM employees '
            . 'LEFT JOIN departments ON departments.id = employees.department_id '
            . 'ORDER BY employees.full_name ASC, employees.id ASC'
        );

        return $statement->fetchAll();
    }
}
