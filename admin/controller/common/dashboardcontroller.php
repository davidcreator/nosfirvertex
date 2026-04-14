<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Controller\Common;

use AureaVertex\Admin\Model\ResumeModel;
use AureaVertex\Admin\Model\TemplateModel;
use AureaVertex\Admin\Model\UserModel;
use AureaVertex\System\Engine\Controller;

class DashboardController extends Controller
{
    public function index(): string
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }

        $userModel = new UserModel($this->registry);
        $resumeModel = new ResumeModel($this->registry);
        $templateModel = new TemplateModel($this->registry);

        return $this->page('common/dashboard', [
            'total_users' => $userModel->getTotal(),
            'total_resumes' => $resumeModel->getTotal(),
            'total_templates' => $templateModel->getTotal(),
        ]);
    }

    public function notFound(): string
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }

        $this->response->addHeader('HTTP/1.1 404 Not Found');

        return $this->page('common/not_found', []);
    }
}
