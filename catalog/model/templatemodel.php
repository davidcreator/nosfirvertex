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

        $templates = $this->db->fetchAll('SELECT template_id, name, category, image_path, description FROM templates WHERE is_active = 1 ORDER BY category, name');

        return $templates !== [] ? $templates : $this->fallbackTemplates();
    }

    private function fallbackTemplates(): array
    {
        return [
            ['template_id' => 0, 'name' => 'Básico Essencial', 'category' => 'basico', 'image_path' => 'image/templates/basico.svg', 'description' => 'Estrutura clara para começar.'],
            ['template_id' => 0, 'name' => 'Moderno Dinâmico', 'category' => 'moderno', 'image_path' => 'image/templates/moderno.svg', 'description' => 'Visual moderno com leitura fluida.'],
            ['template_id' => 0, 'name' => 'Profissional Executivo', 'category' => 'profissional', 'image_path' => 'image/templates/profissional.svg', 'description' => 'Para cargos com foco em senioridade.'],
            ['template_id' => 0, 'name' => 'Criativo Portfólio', 'category' => 'criativo', 'image_path' => 'image/templates/criativo.svg', 'description' => 'Para áreas criativas e autorais.'],
            ['template_id' => 0, 'name' => 'Minimalista Premium', 'category' => 'minimalista', 'image_path' => 'image/templates/minimalista.svg', 'description' => 'Leitura limpa e elegante.'],
        ];
    }
}
