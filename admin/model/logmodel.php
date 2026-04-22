<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class LogModel extends Model
{
    public function getLogs(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if ($this->db === null) {
            return [];
        }

        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildFilters($filters);

        $rows = $this->db->fetchAll(
            'SELECT log_id, context, level, message, metadata, created_at
             FROM logs'
            . $whereSql
            . ' ORDER BY log_id DESC'
            . ' LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );

        foreach ($rows as &$row) {
            $metadata = json_decode((string) ($row['metadata'] ?? ''), true);
            $row['request_id'] = is_array($metadata) && is_string($metadata['request_id'] ?? null)
                ? (string) $metadata['request_id']
                : '-';
        }
        unset($row);

        return $rows;
    }

    public function countLogs(array $filters = []): int
    {
        if ($this->db === null) {
            return 0;
        }

        [$whereSql, $params] = $this->buildFilters($filters);
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS total
             FROM logs'
            . $whereSql,
            $params
        );

        return (int) ($row['total'] ?? 0);
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

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $clauses[] = '(message LIKE :query OR context LIKE :query OR metadata LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $level = strtolower(trim((string) ($filters['level'] ?? '')));
        if (in_array($level, ['info', 'warning', 'error'], true)) {
            $clauses[] = 'level = :level';
            $params[':level'] = $level;
        }

        $context = trim((string) ($filters['context'] ?? ''));
        if ($context !== '' && preg_match('/^[a-zA-Z0-9_\-]{1,80}$/', $context) === 1) {
            $clauses[] = 'context = :context';
            $params[':context'] = $context;
        }

        $requestId = strtolower(trim((string) ($filters['request_id'] ?? '')));
        if ($requestId !== '' && preg_match('/^[a-z0-9\-]{6,64}$/', $requestId) === 1) {
            $clauses[] = 'metadata LIKE :request_id';
            $params[':request_id'] = '%"request_id":"' . $requestId . '"%';
        }

        $createdFrom = trim((string) ($filters['created_from'] ?? ''));
        if ($this->isDateString($createdFrom)) {
            $clauses[] = 'created_at >= :created_from';
            $params[':created_from'] = $createdFrom . ' 00:00:00';
        }

        $createdTo = trim((string) ($filters['created_to'] ?? ''));
        if ($this->isDateString($createdTo)) {
            $clauses[] = 'created_at <= :created_to';
            $params[':created_to'] = $createdTo . ' 23:59:59';
        }

        if ($clauses === []) {
            return ['', []];
        }

        return [' WHERE ' . implode(' AND ', $clauses), $params];
    }

    private function isDateString(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year);
    }
}
