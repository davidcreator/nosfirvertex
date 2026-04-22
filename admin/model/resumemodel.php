<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class ResumeModel extends Model
{
    public function getResumes(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if ($this->db === null) {
            return [];
        }

        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildFilters($filters);

        return $this->db->fetchAll(
            'SELECT r.resume_id, r.title, r.status, r.updated_at, u.full_name, u.email, t.name AS template_name
             FROM resumes r
             INNER JOIN users u ON u.user_id = r.user_id
             LEFT JOIN templates t ON t.template_id = r.template_id'
             . $whereSql
             . ' ORDER BY COALESCE(r.updated_at, r.created_at) DESC, r.resume_id DESC'
             . ' LIMIT ' . $perPage . ' OFFSET ' . $offset,
            $params
        );
    }

    public function countResumes(array $filters = []): int
    {
        if ($this->db === null) {
            return 0;
        }

        [$whereSql, $params] = $this->buildFilters($filters);
        $row = $this->db->fetch(
            'SELECT COUNT(*) AS total
             FROM resumes r
             INNER JOIN users u ON u.user_id = r.user_id'
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

        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM resumes');

        return (int) ($row['total'] ?? 0);
    }

    private function buildFilters(array $filters): array
    {
        $clauses = [];
        $params = [];

        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $clauses[] = '(r.title LIKE :query OR u.full_name LIKE :query OR u.email LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if (in_array($status, ['draft', 'published'], true)) {
            $clauses[] = 'r.status = :status';
            $params[':status'] = $status;
        }

        $updatedFrom = trim((string) ($filters['updated_from'] ?? ''));
        if ($this->isDateString($updatedFrom)) {
            $clauses[] = 'COALESCE(r.updated_at, r.created_at) >= :updated_from';
            $params[':updated_from'] = $updatedFrom . ' 00:00:00';
        }

        $updatedTo = trim((string) ($filters['updated_to'] ?? ''));
        if ($this->isDateString($updatedTo)) {
            $clauses[] = 'COALESCE(r.updated_at, r.created_at) <= :updated_to';
            $params[':updated_to'] = $updatedTo . ' 23:59:59';
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
