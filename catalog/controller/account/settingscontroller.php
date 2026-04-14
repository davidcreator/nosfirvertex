<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Account;

use NosfirVertex\Catalog\Model\UserModel;
use NosfirVertex\System\Engine\Controller;

class SettingsController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $userModel = new UserModel($this->registry);
        $userId = (int) $this->auth->id();

        if ($this->request->isPost() && $this->validateCsrfToken()) {
            $payload = [
                'full_name' => (string) $this->request->post('full_name', ''),
                'phone' => (string) $this->request->post('phone', ''),
                'city' => (string) $this->request->post('city', ''),
                'state' => (string) $this->request->post('state', ''),
                'country' => (string) $this->request->post('country', ''),
                'website' => (string) $this->request->post('website', ''),
                'linkedin' => (string) $this->request->post('linkedin', ''),
                'github' => (string) $this->request->post('github', ''),
                'new_password' => (string) $this->request->post('new_password', ''),
            ];

            $userModel->updateAccount($userId, $payload);
            $this->flash('success', 'Configurações atualizadas.');
            $this->redirect('catalog/index.php?route=account/settings');
        }

        $user = $userModel->getById($userId);

        return $this->page('account/settings', [
            'user' => $user,
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->flash('error', 'Faça login para acessar as configurações.');
            $this->redirect('catalog/index.php?route=login');
        }
    }
}
