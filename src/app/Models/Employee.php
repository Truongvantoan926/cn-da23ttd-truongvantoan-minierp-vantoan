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

    public function getPaginatedWithDepartment(int $page, int $perPage): array
    {
        return $this->paginate(
            'SELECT employees.*, departments.name AS department_name '
            . 'FROM employees '
            . 'LEFT JOIN departments ON departments.id = employees.department_id '
            . 'ORDER BY employees.full_name ASC, employees.id ASC',
            'SELECT COUNT(*) FROM employees',
            [],
            $page,
            $perPage
        );
    }

    public function getByDepartmentIdWithRole(int|string $departmentId): array
    {
        $statement = $this->db->prepare(
            'SELECT employees.*, '
            . 'COALESCE(NULLIF(employees.position, \'\'), roles.name) AS role_name '
            . 'FROM employees '
            . 'LEFT JOIN users ON users.employee_id = employees.id '
            . 'LEFT JOIN roles ON roles.id = users.role_id '
            . 'WHERE employees.department_id = :department_id '
            . 'ORDER BY employees.full_name ASC, employees.id ASC'
        );
        $statement->execute(['department_id' => $departmentId]);

        return $statement->fetchAll();
    }

    public function getPaginatedByDepartmentIdWithRole(
        int|string $departmentId,
        int $page,
        int $perPage
    ): array {
        $where = ' WHERE employees.department_id = :department_id';

        return $this->paginate(
            'SELECT employees.*, '
                . 'COALESCE(NULLIF(employees.position, \'\'), roles.name) AS role_name '
                . 'FROM employees '
                . 'LEFT JOIN users ON users.employee_id = employees.id '
                . 'LEFT JOIN roles ON roles.id = users.role_id'
                . $where
                . ' ORDER BY employees.full_name ASC, employees.id ASC',
            'SELECT COUNT(*) FROM employees' . $where,
            [':department_id' => $departmentId],
            $page,
            $perPage
        );
    }
}
