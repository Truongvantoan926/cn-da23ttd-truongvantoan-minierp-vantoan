<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Customer extends Model
{
    protected string $table = 'customers';

    public function getPaginated(int $page, int $perPage): array
    {
        return $this->paginate(
            'SELECT * FROM customers ORDER BY name ASC, id ASC',
            'SELECT COUNT(*) FROM customers',
            [],
            $page,
            $perPage
        );
    }
}
