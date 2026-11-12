<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

final class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    public function log(string $action, string $description): void
    {
        if (!preg_match('/\A[a-zA-Z][a-zA-Z0-9._-]{0,99}\z/', $action)) {
            throw new InvalidArgumentException('Hành động nhật ký không hợp lệ.');
        }

        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            $userId = null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if (
            !is_string($ip)
            || strlen($ip) > 45
            || filter_var($ip, FILTER_VALIDATE_IP) === false
        ) {
            $ip = null;
        }

        $statement = $this->db->prepare(
            'INSERT INTO activity_logs (user_id, action, description, ip_address) '
            . 'VALUES (:user_id, :action, :description, :ip_address)'
        );
        $statement->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
        ]);
    }

    public function getRecent(int $limit = 50): array
    {
        $limit = max(1, min(50, $limit));
        $statement = $this->db->query(
            'SELECT activity_logs.id, activity_logs.user_id, activity_logs.action, '
            . 'activity_logs.description, activity_logs.ip_address, activity_logs.created_at, '
            . 'COALESCE(employees.full_name, users.username, \'Tài khoản không còn tồn tại\') '
            . 'AS user_name '
            . 'FROM activity_logs '
            . 'LEFT JOIN users ON users.id = activity_logs.user_id '
            . 'LEFT JOIN employees ON employees.id = users.employee_id '
            . 'ORDER BY activity_logs.created_at DESC, activity_logs.id DESC '
            . 'LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }

    public function getPaginated(int $page, int $perPage): array
    {
        $from = ' FROM activity_logs '
            . 'LEFT JOIN users ON users.id = activity_logs.user_id '
            . 'LEFT JOIN employees ON employees.id = users.employee_id';

        return $this->paginate(
            'SELECT activity_logs.id, activity_logs.user_id, activity_logs.action, '
                . 'activity_logs.description, activity_logs.ip_address, activity_logs.created_at, '
                . 'COALESCE(employees.full_name, users.username, \'Tài khoản không còn tồn tại\') '
                . 'AS user_name'
                . $from
                . ' ORDER BY activity_logs.created_at DESC, activity_logs.id DESC',
            'SELECT COUNT(*)' . $from,
            [],
            $page,
            $perPage
        );
    }
}
