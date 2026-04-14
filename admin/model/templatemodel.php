<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Model;

use AureaVertex\System\Engine\Model;

class TemplateModel extends Model
{
    public function getTemplates(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll('SELECT * FROM templates ORDER BY category, name');
    }

    public function save(array $data): void
    {
        if ($this->db === null) {
            return;
        }

        $templateId = (int) ($data['template_id'] ?? 0);

        if ($templateId > 0) {
            $this->db->execute(
                'UPDATE templates
                 SET name = :name,
                     category = :category,
                     image_path = :image_path,
                     description = :description,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE template_id = :template_id',
                [
                    ':name' => trim((string) ($data['name'] ?? '')),
                    ':category' => trim((string) ($data['category'] ?? '')),
                    ':image_path' => trim((string) ($data['image_path'] ?? '')),
                    ':description' => trim((string) ($data['description'] ?? '')),
                    ':is_active' => !empty($data['is_active']) ? 1 : 0,
                    ':template_id' => $templateId,
                ]
            );

            return;
        }

        $this->db->execute(
            'INSERT INTO templates (name, category, image_path, description, is_active, created_at)
             VALUES (:name, :category, :image_path, :description, :is_active, NOW())',
            [
                ':name' => trim((string) ($data['name'] ?? '')),
                ':category' => trim((string) ($data['category'] ?? '')),
                ':image_path' => trim((string) ($data['image_path'] ?? '')),
                ':description' => trim((string) ($data['description'] ?? '')),
                ':is_active' => !empty($data['is_active']) ? 1 : 0,
            ]
        );
    }

    public function getTotal(): int
    {
        if ($this->db === null) {
            return 0;
        }

        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM templates');

        return (int) ($row['total'] ?? 0);
    }
}
