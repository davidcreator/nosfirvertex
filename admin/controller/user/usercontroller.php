<?php
declare(strict_types=1);

namespace AureaVertex\Admin\Controller\User;

use AureaVertex\Admin\Model\UserModel;
use AureaVertex\System\Engine\Controller;

class UserController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new UserModel($this->registry);

        return $this->page('user/index', [
            'users' => $model->getUsers(),
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
