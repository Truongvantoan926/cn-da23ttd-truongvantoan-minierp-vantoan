<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class User extends Model
{
    protected string $table = 'users';

    public function findByUsername(string $username): array|false
    {
        $statement = $this->db->prepare(
            'SELECT * FROM users WHERE username = :username LIMIT 1'
        );
        $statement->execute(['username' => $username]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function findRoleLevelById(int $roleId): ?int
    {
        $statement = $this->db->prepare(
            'SELECT level FROM roles WHERE id = :role_id LIMIT 1'
        );
        $statement->execute(['role_id' => $roleId]);
        $level = $statement->fetchColumn();

        return $level === false ? null : (int) $level;
    }
}
