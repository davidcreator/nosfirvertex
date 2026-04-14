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
                    ':name' => trim((string) ($data['name'] ?? '')),
                    ':position_code' => trim((string) ($data['position_code'] ?? '')),
                    ':content_html' => trim((string) ($data['content_html'] ?? '')),
                    ':is_active' => !empty($data['is_active']) ? 1 : 0,
                    ':display_order' => (int) ($data['display_order'] ?? 0),
                    ':ad_block_id' => $adId,
                ]
            );

            return;
        }

        $this->db->execute(
            'INSERT INTO ad_blocks (name, position_code, content_html, is_active, display_order, created_at)
             VALUES (:name, :position_code, :content_html, :is_active, :display_order, NOW())',
            [
                ':name' => trim((string) ($data['name'] ?? '')),
                ':position_code' => trim((string) ($data['position_code'] ?? '')),
                ':content_html' => trim((string) ($data['content_html'] ?? '')),
                ':is_active' => !empty($data['is_active']) ? 1 : 0,
                ':display_order' => (int) ($data['display_order'] ?? 0),
            ]
        );
    }
}
