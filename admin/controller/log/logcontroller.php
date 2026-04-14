<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Controller\Log;

use AureaVertex\Admin\Model\LogModel;
use AureaVertex\System\Engine\Controller;

class LogController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new LogModel($this->registry);

        return $this->page('log/index', [
            'logs' => $model->getLogs(),
            'file_log_tail' => $model->getFileLogTail(),
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
