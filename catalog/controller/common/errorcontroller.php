<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Common;

use NosfirVertex\System\Engine\Controller;

class ErrorController extends Controller
{
    public function notFound(): string
    {
        $this->response->addHeader('HTTP/1.1 404 Not Found');

        return $this->page('common/not_found', []);
    }
}
