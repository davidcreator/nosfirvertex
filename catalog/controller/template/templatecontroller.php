<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Template;

use NosfirVertex\Catalog\Model\TemplateModel;
use NosfirVertex\System\Engine\Controller;

class TemplateController extends Controller
{
    public function index(): string
    {
        $templateModel = new TemplateModel($this->registry);

        return $this->page('template/index', [
            'templates' => $templateModel->getActiveTemplates(),
        ]);
    }
}
