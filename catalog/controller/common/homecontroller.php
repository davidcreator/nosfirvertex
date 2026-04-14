<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Common;

use AureaVertex\Catalog\Model\AdModel;
use AureaVertex\Catalog\Model\TemplateModel;
use AureaVertex\System\Engine\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        $templateModel = new TemplateModel($this->registry);
        $adModel = new AdModel($this->registry);

        return $this->page('common/home', [
            'templates' => $templateModel->getActiveTemplates(),
            'ads_top' => $adModel->getByPosition('home_top'),
            'ads_mid' => $adModel->getByPosition('home_mid'),
            'ads_footer' => $adModel->getByPosition('home_footer'),
        ]);
    }
}
