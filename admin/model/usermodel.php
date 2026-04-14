<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class UserModel extends Model
{
    public function getUsers(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll(
            'SELECT user_id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 200'
        );
    }

    public function getTotal(): int
    {
        if ($this->db === null) {
            return 0;
        }

        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM users WHERE role = :role', [':role' => 'user']);

        return (int) ($row['total'] ?? 0);
    }
}
