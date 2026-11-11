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

    public function getProfileById(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT users.id, users.username, users.role_id, users.employee_id, '
            . 'COALESCE(NULLIF(employees.full_name, \'\'), NULLIF(users.full_name, \'\'), users.username) '
            . 'AS full_name, '
            . 'COALESCE(NULLIF(employees.phone, \'\'), users.phone, \'\') AS phone, '
            . 'roles.name AS role_name '
            . 'FROM users '
            . 'LEFT JOIN employees ON employees.id = users.employee_id '
            . 'LEFT JOIN roles ON roles.id = users.role_id '
            . 'WHERE users.id = :user_id LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch(PDO::FETCH_ASSOC);

        return $profile === false ? null : $profile;
    }

    public function updateProfile(int $userId, string $fullName, ?string $phone): int
    {
        $statement = $this->db->prepare(
            'UPDATE users '
            . 'LEFT JOIN employees ON employees.id = users.employee_id '
            . 'SET users.full_name = :user_full_name, users.phone = :user_phone, '
            . 'employees.full_name = :employee_full_name, employees.phone = :employee_phone '
            . 'WHERE users.id = :user_id'
        );
        $statement->execute([
            'user_full_name' => $fullName,
            'user_phone' => $phone,
            'employee_full_name' => $fullName,
            'employee_phone' => $phone,
            'user_id' => $userId,
        ]);

        return $statement->rowCount();
    }

    public function findPasswordHashById(int $userId): ?string
    {
        $statement = $this->db->prepare(
            'SELECT password_hash FROM users WHERE id = :user_id LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $passwordHash = $statement->fetchColumn();

        return is_string($passwordHash) ? $passwordHash : null;
    }

    public function updatePasswordHash(int $userId, string $passwordHash): int
    {
        $statement = $this->db->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id = :user_id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'user_id' => $userId,
        ]);

        return $statement->rowCount();
    }
}
