<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class UserModel extends Model
{
    public function getUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if ($this->db === null) {
            return [];
        }

        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildFilters($filters);

        return $this->db->fetchAll(
            'SELECT user_id, full_name, email, role, status, created_at
             FROM users'
            . $whereSql
            . ' ORDER BY created_at DESC, user_id DESC'
            . ' LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );
    }

    public function countUsers(array $filters = []): int
    {
        if ($this->db === null) {
            return 0;
        }

        [$whereSql, $params] = $this->buildFilters($filters);
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS total
             FROM users'
            . $whereSql,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function getTotal(): int
    {
        if ($this->db === null) {
            return 0;
        }

        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM users WHERE role = :role', [':role' => 'user']);

        return (int) ($row['total'] ?? 0);
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $clauses[] = '(full_name LIKE :query OR email LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $role = strtolower(trim((string) ($filters['role'] ?? '')));
        if (in_array($role, ['user', 'admin'], true)) {
            $clauses[] = 'role = :role';
            $params[':role'] = $role;
        }

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if (in_array($status, ['active', 'inactive'], true)) {
            $clauses[] = 'status = :status';
            $params[':status'] = $status;
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
