<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Template;

use AureaVertex\Catalog\Model\TemplateModel;
use AureaVertex\System\Engine\Controller;

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
