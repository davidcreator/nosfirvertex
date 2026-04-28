<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Common;

use NosfirVertex\System\Engine\Controller;

class LoginController extends Controller
{
    public function index(): string
    {
        if ($this->auth->check()) {
            $this->redirect('admin/index.php?route=dashboard');
        }

        $error = '';

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $error = $this->lang('Token de segurança inválido.');
            } else {
                $email = (string) $this->request->post('email', '');
                $password = (string) $this->request->post('password', '');

                if ($this->auth->attempt($email, $password)) {
                    $this->flash('success', $this->lang('Login administrativo realizado.'));
                    $this->redirect('admin/index.php?route=dashboard');
                }

                $error = $this->lang('Credenciais administrativas inválidas.');
            }
        }

        return $this->page('common/login', [
            'error' => $error,
        ]);
    }

    public function logout(): never
    {
        if (!$this->request->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', $this->lang('Requisição inválida para logout.'));
            $this->redirect('admin/index.php?route=dashboard');
        }

        $this->auth->logout();
        $this->flash('success', $this->lang('Sessão administrativa encerrada.'));
        $this->redirect('admin/index.php?route=login');
    }
}
