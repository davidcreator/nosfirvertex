<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Model;

use NosfirVertex\System\Engine\Model;

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
