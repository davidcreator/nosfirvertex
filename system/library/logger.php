<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Logger
{
    public function __construct(private readonly string $logFile, private readonly Database|null $db = null)
    {
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $line = sprintf("[%s] %s: %s %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message, json_encode($context, JSON_UNESCAPED_UNICODE));
        file_put_contents($this->logFile, $line, FILE_APPEND);

        if ($this->db === null) {
            return;
        }

        try {
            $this->db->execute(
                'INSERT INTO logs (context, level, message, metadata, created_at) VALUES (:context, :level, :message, :metadata, NOW())',
                [
                    ':context' => $context['context'] ?? 'system',
                    ':level' => $level,
                    ':message' => $message,
                    ':metadata' => json_encode($context, JSON_UNESCAPED_UNICODE),
                ]
            );
        } catch (\Throwable) {
            // If DB logging fails, keep file logging as fallback.
        }
    }
}
