<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class SettingModel extends Model
{
    public function getSettings(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll('SELECT setting_id, `key`, `value`, is_public, autoload FROM settings ORDER BY `key`');
    }

    public function save(string $key, string $value): void
    {
        if ($this->db === null) {
            return;
        }

        $existing = $this->db->fetch('SELECT setting_id FROM settings WHERE `key` = :key LIMIT 1', [':key' => $key]);

        if ($existing === null) {
            $this->db->execute('INSERT INTO settings (`key`, `value`, is_public, autoload, created_at) VALUES (:key, :value, 0, 1, NOW())', [
                ':key' => $key,
                ':value' => $value,
            ]);

            return;
        }

        $this->db->execute('UPDATE settings SET `value` = :value, updated_at = NOW() WHERE `key` = :key', [
            ':key' => $key,
            ':value' => $value,
        ]);
    }
}
