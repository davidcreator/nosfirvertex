<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Common;

use AureaVertex\System\Engine\Controller;

class ErrorController extends Controller
{
    public function notFound(): string
    {
        $this->response->addHeader('HTTP/1.1 404 Not Found');

        return $this->page('common/not_found', []);
    }
}
