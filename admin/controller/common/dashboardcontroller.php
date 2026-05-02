<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Common;

use NosfirVertex\Admin\Model\AdModel;
use NosfirVertex\Admin\Model\LogModel;
use NosfirVertex\Admin\Model\ResumeModel;
use NosfirVertex\Admin\Model\TemplateModel;
use NosfirVertex\Admin\Model\UserModel;
use NosfirVertex\System\Engine\Controller;

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
        $adModel = new AdModel($this->registry);
        $logModel = new LogModel($this->registry);

        return $this->page('common/dashboard', [
            'total_users' => $userModel->getTotal(),
            'total_resumes' => $resumeModel->getTotal(),
            'total_templates' => $templateModel->getTotal(),
            'total_ads' => count($adModel->getAds()),
            'total_logs' => $logModel->countLogs(),
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
