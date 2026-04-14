<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Model;

use AureaVertex\System\Engine\Model;

class SettingModel extends Model
{
    public function get(string $key, string $default = ''): string
    {
        if ($this->db === null) {
            return $default;
        }

        $row = $this->db->fetch('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1', [':key' => $key]);

        if ($row === null) {
            return $default;
        }

        return (string) ($row['value'] ?? $default);
    }
}
