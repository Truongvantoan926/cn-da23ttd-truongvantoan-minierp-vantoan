<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Supplier extends Model
{
    protected string $table = 'suppliers';

    public function getPaginated(int $page, int $perPage): array
    {
        return $this->paginate(
            'SELECT * FROM suppliers ORDER BY name ASC, id ASC',
            'SELECT COUNT(*) FROM suppliers',
            [],
            $page,
            $perPage
        );
    }
}
