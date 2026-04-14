<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Common;

use AureaVertex\System\Engine\Controller;

class ThemeController extends Controller
{
    public function toggle(): never
    {
        $current = $this->session->get('theme', 'light');
        $next = $current === 'dark' ? 'light' : 'dark';

        $this->session->set('theme', $next);

        $referer = (string) ($this->request->server('HTTP_REFERER') ?? '');
        if ($referer !== '') {
            $this->response->redirect($referer);
        }

        $this->redirect('catalog/index.php');
    }
}
