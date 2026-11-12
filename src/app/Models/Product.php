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

    public function search(string $keyword): array
    {
        $statement = $this->db->prepare(
            'SELECT products.*, categories.name AS category_name '
            . 'FROM products '
            . 'LEFT JOIN categories ON categories.id = products.category_id '
            . 'WHERE products.name LIKE :name_keyword OR products.sku LIKE :sku_keyword '
            . 'ORDER BY products.name ASC, products.id ASC'
        );
        $keyword = '%' . $keyword . '%';
        $statement->execute([
            'name_keyword' => $keyword,
            'sku_keyword' => $keyword,
        ]);

        return $statement->fetchAll();
    }

    public function getPaginatedWithCategory(string $keyword, int $page, int $perPage): array
    {
        $where = $keyword === ''
            ? ''
            : ' WHERE products.name LIKE :name_keyword OR products.sku LIKE :sku_keyword';
        $parameters = $keyword === ''
            ? []
            : [
                ':name_keyword' => '%' . $keyword . '%',
                ':sku_keyword' => '%' . $keyword . '%',
            ];
        $from = ' FROM products '
            . 'LEFT JOIN categories ON categories.id = products.category_id';

        return $this->paginate(
            'SELECT products.*, categories.name AS category_name'
                . $from . $where
                . ' ORDER BY products.name ASC, products.id ASC',
            'SELECT COUNT(*)' . $from . $where,
            $parameters,
            $page,
            $perPage
        );
    }

    public function findByIdWithCategory(int|string $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT products.*, categories.name AS category_name '
            . 'FROM products '
            . 'LEFT JOIN categories ON categories.id = products.category_id '
            . 'WHERE products.id = :id '
            . 'LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();

        return $product === false ? null : $product;
    }

    public function getByCategoryId(int|string $categoryId): array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM products '
            . 'WHERE category_id = :category_id '
            . 'ORDER BY name ASC, id ASC'
        );
        $statement->execute(['category_id' => $categoryId]);

        return $statement->fetchAll();
    }

    public function getPaginatedByCategoryId(
        int|string $categoryId,
        int $page,
        int $perPage
    ): array {
        return $this->paginate(
            'SELECT * FROM products '
                . 'WHERE category_id = :category_id '
                . 'ORDER BY name ASC, id ASC',
            'SELECT COUNT(*) FROM products WHERE category_id = :category_id',
            [':category_id' => $categoryId],
            $page,
            $perPage
        );
    }

    public function getLowStockProducts(): array
    {
        $statement = $this->db->query(
            'SELECT products.*, categories.name AS category_name '
            . 'FROM products '
            . 'LEFT JOIN categories ON categories.id = products.category_id '
            . 'WHERE products.stock_qty <= products.min_stock '
            . 'ORDER BY products.stock_qty ASC, products.name ASC, products.id ASC'
        );

        return $statement->fetchAll();
    }
}
