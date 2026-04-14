<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Ad;

use NosfirVertex\Admin\Model\AdModel;
use NosfirVertex\System\Engine\Controller;

class AdController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new AdModel($this->registry);

        if ($this->request->isPost() && $this->validateCsrfToken()) {
            $model->save($this->request->allPost());
            $this->flash('success', 'Bloco de anúncio salvo com sucesso.');
            $this->redirect('admin/index.php?route=ads');
        }

        return $this->page('ad/index', [
            'ads' => $model->getAds(),
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
