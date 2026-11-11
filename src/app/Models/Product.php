<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Product extends Model
{
    protected string $table = 'products';

    public function getAllWithCategory(): array
    {
        $statement = $this->db->query(
            'SELECT products.*, categories.name AS category_name '
            . 'FROM products '
            . 'LEFT JOIN categories ON categories.id = products.category_id '
            . 'ORDER BY products.name ASC, products.id ASC'
        );

        return $statement->fetchAll();
    }
}
