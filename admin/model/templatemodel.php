<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class TemplateModel extends Model
{
    public function getTemplates(): array
    {
        if ($this->db === null) {
            return [];
        }

        $this->ensureDefaultTemplates();

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

    private function ensureDefaultTemplates(): void
    {
        if ($this->db === null) {
            return;
        }

        $rows = $this->db->fetchAll('SELECT category FROM templates');
        $existingCategories = [];

        foreach ($rows as $row) {
            $category = strtolower(trim((string) ($row['category'] ?? '')));
            if ($category !== '') {
                $existingCategories[$category] = true;
            }
        }

        foreach ($this->defaultTemplates() as $template) {
            $category = strtolower((string) ($template['category'] ?? ''));
            if ($category === '' || isset($existingCategories[$category])) {
                continue;
            }

            $this->db->execute(
                'INSERT INTO templates (name, category, image_path, description, is_active, created_at)
                 VALUES (:name, :category, :image_path, :description, 1, NOW())',
                [
                    ':name' => (string) $template['name'],
                    ':category' => (string) $template['category'],
                    ':image_path' => (string) $template['image_path'],
                    ':description' => (string) $template['description'],
                ]
            );

            $existingCategories[$category] = true;
        }
    }

    private function defaultTemplates(): array
    {
        return [
            ['name' => 'Basico Essencial', 'category' => 'basico', 'image_path' => 'image/templates/basico.svg', 'description' => 'Estrutura clara para comecar.'],
            ['name' => 'Moderno Dinamico', 'category' => 'moderno', 'image_path' => 'image/templates/moderno.svg', 'description' => 'Visual moderno com leitura fluida.'],
            ['name' => 'Profissional Executivo', 'category' => 'profissional', 'image_path' => 'image/templates/profissional.svg', 'description' => 'Para cargos com foco em senioridade.'],
            ['name' => 'Criativo Portfolio', 'category' => 'criativo', 'image_path' => 'image/templates/criativo.svg', 'description' => 'Para areas criativas e autorais.'],
            ['name' => 'Minimalista Premium', 'category' => 'minimalista', 'image_path' => 'image/templates/minimalista.svg', 'description' => 'Leitura limpa e elegante.'],
            ['name' => 'Colunas 25-75', 'category' => 'coluna2575', 'image_path' => 'image/templates/coluna2575.svg', 'description' => 'Coluna lateral esquerda compacta e area principal ampla.'],
            ['name' => 'Colunas 75-25', 'category' => 'coluna7525', 'image_path' => 'image/templates/coluna7525.svg', 'description' => 'Area principal a esquerda e coluna lateral de apoio.'],
        ];
    }
}
