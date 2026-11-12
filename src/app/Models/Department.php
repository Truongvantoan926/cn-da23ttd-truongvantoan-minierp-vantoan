<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Department extends Model
{
    protected string $table = 'departments';

    public function getPaginated(int $page, int $perPage): array
    {
        return $this->paginate(
            'SELECT * FROM departments ORDER BY name ASC, id ASC',
            'SELECT COUNT(*) FROM departments',
            [],
            $page,
            $perPage
        );
    }
}
