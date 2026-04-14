<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Account;

use NosfirVertex\Catalog\Model\UserModel;
use NosfirVertex\System\Engine\Controller;
use NosfirVertex\System\Library\Validator;

class AuthController extends Controller
{
    public function login(): string
    {
        if ($this->auth->check()) {
            $this->redirect('catalog/index.php?route=dashboard');
        }

        $error = '';

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $error = 'Token de segurança inválido. Atualize a página.';
            } else {
                $email = (string) $this->request->post('email', '');
                $password = (string) $this->request->post('password', '');

                if ($this->auth->attempt($email, $password)) {
                    $this->flash('success', 'Login realizado com sucesso.');
                    $this->redirect('catalog/index.php?route=dashboard');
                }

                $error = 'E-mail ou senha inválidos.';
            }
        }

        return $this->page('account/login', [
            'error' => $error,
        ]);
    }

    public function register(): string
    {
        if ($this->auth->check()) {
            $this->redirect('catalog/index.php?route=dashboard');
        }

        $error = '';

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $error = 'Token de segurança inválido. Atualize a página.';
            } else {
                $fullName = (string) $this->request->post('full_name', '');
                $email = (string) $this->request->post('email', '');
                $password = (string) $this->request->post('password', '');

                if (!Validator::required($fullName) || !Validator::email($email) || !Validator::minLength($password, 8)) {
                    $error = 'Preencha nome, e-mail válido e senha com mínimo de 8 caracteres.';
                } else {
                    $userModel = new UserModel($this->registry);

                    if ($userModel->existsByEmail($email)) {
                        $error = 'Já existe uma conta com este e-mail.';
                    } else {
                        $userModel->createUser($fullName, $email, $password);
                        $this->auth->attempt($email, $password);
                        $this->flash('success', 'Conta criada com sucesso.');
                        $this->redirect('catalog/index.php?route=dashboard');
                    }
                }
            }
        }

        return $this->page('account/register', [
            'error' => $error,
        ]);
    }

    public function forgot(): string
    {
        $message = '';

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $message = 'Token de segurança inválido.';
            } else {
                $email = (string) $this->request->post('email', '');
                $userModel = new UserModel($this->registry);
                $token = $userModel->createPasswordReset($email);

                if ($token !== null) {
                    $this->logger->info('Token de recuperação gerado', [
                        'context' => 'catalog',
                        'email' => $email,
                        'token' => $token,
                    ]);
                }

                $message = 'Se o e-mail existir, um processo de recuperação foi registrado com segurança.';
            }
        }

        return $this->page('account/forgot', [
            'message' => $message,
        ]);
    }

    public function logout(): never
    {
        $this->auth->logout();
        $this->flash('success', 'Sessão encerrada com sucesso.');

        $this->redirect('catalog/index.php');
    }
}
