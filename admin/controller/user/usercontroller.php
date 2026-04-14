<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\User;

use NosfirVertex\Admin\Model\UserModel;
use NosfirVertex\System\Engine\Controller;

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
