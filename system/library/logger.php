<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Logger
{
    public function __construct(
        private readonly string $logFile,
        private readonly Database|null $db = null,
        private readonly string|null $requestId = null
    )
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

    public function requestId(): string|null
    {
        return $this->requestId;
    }

    private function write(string $level, string $message, array $context = []): void
    {
        $contextWithRequestId = $this->attachRequestId($context);
        $jsonContext = json_encode($contextWithRequestId, JSON_UNESCAPED_UNICODE);
        $requestIdForLine = $this->requestId ?? '-';

        $line = sprintf(
            "[%s] [%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            $requestIdForLine,
            strtoupper($level),
            $message,
            $jsonContext === false ? '{}' : $jsonContext
        );

        file_put_contents($this->logFile, $line, FILE_APPEND);

        if ($this->db === null) {
            return;
        }

        try {
            $metadata = json_encode($contextWithRequestId, JSON_UNESCAPED_UNICODE);
            $this->db->execute(
                'INSERT INTO logs (context, level, message, metadata, created_at) VALUES (:context, :level, :message, :metadata, NOW())',
                [
                    ':context' => $contextWithRequestId['context'] ?? 'system',
                    ':level' => $level,
                    ':message' => $message,
                    ':metadata' => $metadata === false ? '{}' : $metadata,
                ]
            );
        } catch (\Throwable) {
            // If DB logging fails, keep file logging as fallback.
        }
    }

    private function attachRequestId(array $context): array
    {
        if ($this->requestId !== null && $this->requestId !== '' && !isset($context['request_id'])) {
            $context['request_id'] = $this->requestId;
        }

        return $context;
    }
}
