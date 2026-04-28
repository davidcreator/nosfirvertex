<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Model;

use NosfirVertex\System\Engine\Model;

class TemplateModel extends Model
{
    public function getActiveTemplates(): array
    {
        if ($this->db === null) {
            return $this->fallbackTemplates();
        }

        $this->ensureDefaultTemplates();

        $templates = $this->db->fetchAll('SELECT template_id, name, category, image_path, description FROM templates WHERE is_active = 1 ORDER BY category, name');

        return $templates !== [] ? $templates : $this->fallbackTemplates();
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

    private function fallbackTemplates(): array
    {
        return array_map(
            static fn (array $template): array => ['template_id' => 0] + $template,
            $this->defaultTemplates()
        );
    }
}
