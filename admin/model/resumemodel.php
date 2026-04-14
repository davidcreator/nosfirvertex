<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Model;

use AureaVertex\System\Engine\Model;

class ResumeModel extends Model
{
    public function getResumes(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll(
            'SELECT r.resume_id, r.title, r.status, r.updated_at, u.full_name, u.email, t.name AS template_name
             FROM resumes r
             INNER JOIN users u ON u.user_id = r.user_id
             LEFT JOIN templates t ON t.template_id = r.template_id
             ORDER BY r.updated_at DESC, r.created_at DESC
             LIMIT 300'
        );
    }

    public function getTotal(): int
    {
        if ($this->db === null) {
            return 0;
        }

        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM resumes');

        return (int) ($row['total'] ?? 0);
    }
}
