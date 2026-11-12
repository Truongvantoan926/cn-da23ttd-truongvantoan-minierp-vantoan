<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Category extends Model
{
    protected string $table = 'categories';

    public function search(string $keyword): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM categories '
            . 'WHERE name LIKE :keyword '
            . 'ORDER BY name ASC, id ASC'
        );
        $statement->execute(['keyword' => '%' . $keyword . '%']);

        return $statement->fetchAll();
    }
}
