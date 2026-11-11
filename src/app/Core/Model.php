<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use RuntimeException;

abstract class Model
{
    protected string $table = '';

    protected PDO $db;

    public function __construct(?string $table = null)
    {
        $table = $table ?? $this->table;

        if ($table === '') {
            throw new RuntimeException('Model phải khai báo tên bảng.');
        }

        $this->table = $this->validateIdentifier($table);
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $statement = $this->db->query('SELECT * FROM ' . $this->quotedTable());

        return $statement->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) FROM ' . $this->quotedTable()
        )->fetchColumn();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM ' . $this->quotedTable() . ' WHERE `id` = :id LIMIT 1'
        );
        $this->bindValue($statement, ':id', $id);
        $statement->execute();
        $record = $statement->fetch();

        return $record === false ? null : $record;
    }

    public function insert(array $data): string
    {
        if ($data === []) {
            throw new InvalidArgumentException('Dữ liệu thêm mới không được để trống.');
        }

        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $column => $value) {
            $column = $this->validateIdentifier((string) $column);
            $placeholder = ':value_' . count($values);
            $columns[] = $this->quoteIdentifier($column);
            $placeholders[] = $placeholder;
            $values[$placeholder] = $value;
        }

        $sql = 'INSERT INTO ' . $this->quotedTable()
            . ' (' . implode(', ', $columns) . ')'
            . ' VALUES (' . implode(', ', $placeholders) . ')';
        $statement = $this->db->prepare($sql);
        $this->execute($statement, $values);

        return $this->db->lastInsertId();
    }

    public function update(int|string $id, array $data): int
    {
        if ($data === []) {
            throw new InvalidArgumentException('Dữ liệu cập nhật không được để trống.');
        }

        $assignments = [];
        $values = [];

        foreach ($data as $column => $value) {
            $column = $this->validateIdentifier((string) $column);
            if ($column === 'id') {
                throw new InvalidArgumentException('Không thể cập nhật khóa chính.');
            }

            $placeholder = ':value_' . count($values);
            $assignments[] = $this->quoteIdentifier($column) . ' = ' . $placeholder;
            $values[$placeholder] = $value;
        }

        $values[':id'] = $id;
        $sql = 'UPDATE ' . $this->quotedTable()
            . ' SET ' . implode(', ', $assignments)
            . ' WHERE `id` = :id';
        $statement = $this->db->prepare($sql);
        $this->execute($statement, $values);

        return $statement->rowCount();
    }

    public function delete(int|string $id): int
    {
        $statement = $this->db->prepare(
            'DELETE FROM ' . $this->quotedTable() . ' WHERE `id` = :id'
        );
        $this->bindValue($statement, ':id', $id);
        $statement->execute();

        return $statement->rowCount();
    }

    private function quotedTable(): string
    {
        return $this->quoteIdentifier($this->table);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . $this->validateIdentifier($identifier) . '`';
    }

    private function validateIdentifier(string $identifier): string
    {
        if (!preg_match('/\A[a-zA-Z_][a-zA-Z0-9_]*\z/', $identifier)) {
            throw new InvalidArgumentException('Tên bảng hoặc cột không hợp lệ.');
        }

        return $identifier;
    }

    private function execute(PDOStatement $statement, array $values): void
    {
        foreach ($values as $placeholder => $value) {
            $this->bindValue($statement, $placeholder, $value);
        }

        $statement->execute();
    }

    private function bindValue(PDOStatement $statement, string $placeholder, mixed $value): void
    {
        $type = match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            $value === null => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };

        $statement->bindValue($placeholder, $value, $type);
    }
}
