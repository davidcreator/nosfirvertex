<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Model;

use NosfirVertex\System\Engine\Model;

class AdModel extends Model
{
    public function getAds(): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll('SELECT * FROM ad_blocks ORDER BY position_code, display_order, ad_block_id');
    }

    public function save(array $data): void
    {
        if ($this->db === null) {
            return;
        }

        $adId = (int) ($data['ad_block_id'] ?? 0);
        $normalized = $this->normalizePayload($data);

        if ($adId > 0) {
            $this->db->execute(
                'UPDATE ad_blocks
                 SET name = :name,
                     position_code = :position_code,
                     content_html = :content_html,
                     is_active = :is_active,
                     display_order = :display_order,
                     updated_at = NOW()
                 WHERE ad_block_id = :ad_block_id',
                [
                    ':name' => $normalized['name'],
                    ':position_code' => $normalized['position_code'],
                    ':content_html' => $normalized['content_html'],
                    ':is_active' => $normalized['is_active'],
                    ':display_order' => $normalized['display_order'],
                    ':ad_block_id' => $adId,
                ]
            );

            return;
        }

        $this->db->execute(
            'INSERT INTO ad_blocks (name, position_code, content_html, is_active, display_order, created_at)
             VALUES (:name, :position_code, :content_html, :is_active, :display_order, NOW())',
            [
                ':name' => $normalized['name'],
                ':position_code' => $normalized['position_code'],
                ':content_html' => $normalized['content_html'],
                ':is_active' => $normalized['is_active'],
                ':display_order' => $normalized['display_order'],
            ]
        );
    }

    private function normalizePayload(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            $name = 'Bloco de anúncio';
        }

        $name = mb_substr($name, 0, 150);

        $positionCode = strtolower(trim((string) ($data['position_code'] ?? '')));
        if (!preg_match('/^[a-z0-9_]{3,80}$/', $positionCode)) {
            $positionCode = 'home_mid';
        }

        $contentHtml = \sanitize_html_fragment((string) ($data['content_html'] ?? ''));
        if ($contentHtml === '') {
            $contentHtml = '<p>Conteúdo do anúncio indisponível.</p>';
        }

        $displayOrder = (int) ($data['display_order'] ?? 0);
        if ($displayOrder < 0) {
            $displayOrder = 0;
        }

        if ($displayOrder > 10000) {
            $displayOrder = 10000;
        }

        return [
            'name' => $name,
            'position_code' => $positionCode,
            'content_html' => $contentHtml,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'display_order' => $displayOrder,
        ];
    }
}
