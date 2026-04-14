<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Model;

use AureaVertex\System\Engine\Model;

class LogModel extends Model
{
    public function getLogs(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll('SELECT log_id, context, level, message, created_at FROM logs ORDER BY log_id DESC LIMIT 200');
    }

    public function getFileLogTail(): string
    {
        $file = DIR_SYSTEM . '/storage/logs/app.log';

        if (!is_file($file)) {
            return 'Sem logs em arquivo.';
        }

        $content = file_get_contents($file);
        if (!is_string($content)) {
            return 'Não foi possível ler o log.';
        }

        return mb_substr($content, max(0, mb_strlen($content) - 2500));
    }
}
