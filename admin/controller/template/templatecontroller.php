<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Template;

use NosfirVertex\Admin\Model\TemplateModel;
use NosfirVertex\System\Engine\Controller;

class TemplateController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new TemplateModel($this->registry);

        if ($this->request->isPost() && $this->validateCsrfToken()) {
            $model->save($this->request->allPost());
            $this->flash('success', 'Template salvo com sucesso.');
            $this->redirect('admin/index.php?route=templates');
        }

        return $this->page('template/index', [
            'templates' => $model->getTemplates(),
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
