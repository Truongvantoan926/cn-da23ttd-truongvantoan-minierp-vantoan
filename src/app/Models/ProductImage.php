<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ProductImage extends Model
{
    protected string $table = 'product_images';

    public function getImagesByProductId(int|string $productId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM product_images '
            . 'WHERE product_id = :product_id '
            . 'ORDER BY is_main DESC, id ASC'
        );
        $statement->execute(['product_id' => $productId]);

        return $statement->fetchAll();
    }
}
