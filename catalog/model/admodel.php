<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Model;

use AureaVertex\System\Engine\Model;

class AdModel extends Model
{
    public function getByPosition(string $position): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll(
            'SELECT ad_block_id, name, content_html
             FROM ad_blocks
             WHERE position_code = :position_code AND is_active = 1
             ORDER BY display_order ASC, ad_block_id ASC',
            [':position_code' => $position]
        );
    }
}
