<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Resume;

use NosfirVertex\Admin\Model\ResumeModel;
use NosfirVertex\System\Engine\Controller;

class ResumeController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new ResumeModel($this->registry);

        return $this->page('resume/index', [
            'resumes' => $model->getResumes(),
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
