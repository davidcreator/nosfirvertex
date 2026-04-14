<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Account;

use AureaVertex\Catalog\Model\ResumeModel;
use AureaVertex\System\Engine\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $resumeModel = new ResumeModel($this->registry);
        $resumes = $resumeModel->getByUser((int) $this->auth->id());

        return $this->page('account/dashboard', [
            'resumes' => $resumes,
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->flash('error', 'Faça login para acessar o painel.');
            $this->redirect('catalog/index.php?route=login');
        }
    }
}
