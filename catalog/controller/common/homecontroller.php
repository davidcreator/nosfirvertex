<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Common;

use NosfirVertex\System\Engine\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        return $this->page('common/home', [
            'hide_nav' => true,
        ]);
    }
}
