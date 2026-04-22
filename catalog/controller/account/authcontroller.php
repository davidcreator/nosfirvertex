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
                    $this->sendPasswordResetEmail($email, $token);
                }

                $message = 'Se o e-mail existir, instruções de recuperação foram enviadas.';
            }
        }

        return $this->page('account/forgot', [
            'message' => $message,
        ]);
    }

    public function reset(string $token): string
    {
        $cleanToken = strtolower(trim($token));
        $userModel = new UserModel($this->registry);

        if (!$this->isResetTokenFormatValid($cleanToken) || !$userModel->isPasswordResetTokenValid($cleanToken)) {
            return $this->page('account/reset', [
                'error' => 'O link de recuperação é inválido ou expirou.',
                'can_submit' => false,
            ]);
        }

        $error = '';

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $error = 'Token de segurança inválido.';
            } else {
                $password = (string) $this->request->post('password', '');
                $passwordConfirm = (string) $this->request->post('password_confirm', '');

                if (!Validator::minLength($password, 8)) {
                    $error = 'A nova senha deve ter no mínimo 8 caracteres.';
                } elseif ($password !== $passwordConfirm) {
                    $error = 'A confirmação de senha não confere.';
                } elseif (!$userModel->resetPasswordByToken($cleanToken, $password)) {
                    $error = 'Não foi possível redefinir a senha. Solicite um novo link.';
                } else {
                    $this->flash('success', 'Senha redefinida com sucesso. Faça login para continuar.');
                    $this->redirect('catalog/index.php?route=login');
                }
            }
        }

        return $this->page('account/reset', [
            'error' => $error,
            'can_submit' => true,
        ]);
    }

    public function logout(): never
    {
        if (!$this->request->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Requisição inválida para logout.');
            $this->redirect('catalog/index.php?route=dashboard');
        }

        $this->auth->logout();
        $this->flash('success', 'Sessão encerrada com sucesso.');

        $this->redirect('catalog/index.php');
    }

    private function sendPasswordResetEmail(string $email, string $token): void
    {
        $resetPath = 'catalog/index.php?route=password/reset/' . rawurlencode($token);
        $resetUrl = $this->toAbsoluteUrl(base_url($resetPath));

        $subject = 'Recuperação de senha - NosfirVertex';
        $body = "Olá,\n\n"
            . "Recebemos uma solicitação de recuperação de senha.\n"
            . "Use o link abaixo para definir uma nova senha:\n\n"
            . $resetUrl . "\n\n"
            . "Se você não solicitou essa alteração, ignore esta mensagem.\n"
            . "Este link expira em 1 hora.\n";

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: no-reply@nosfirvertex.local',
        ];

        if (!function_exists('mail')) {
            $this->logger->warning('Função mail() indisponível para recuperação de senha.', [
                'context' => 'catalog',
            ]);

            return;
        }

        $sent = @mail($email, $subject, $body, implode("\r\n", $headers));
        if (!$sent) {
            $this->logger->warning('Falha ao enviar e-mail de recuperação.', [
                'context' => 'catalog',
                'email' => mb_strtolower(trim($email)),
            ]);
        }
    }

    private function toAbsoluteUrl(string $url): string
    {
        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        $scheme = $this->isHttpsRequest() ? 'https' : 'http';
        $host = (string) ($this->request->server('HTTP_HOST') ?? 'localhost');

        return rtrim($scheme . '://' . $host, '/') . '/' . ltrim($url, '/');
    }

    private function isResetTokenFormatValid(string $token): bool
    {
        return preg_match('/^[a-f0-9]{40,128}$/', $token) === 1;
    }

    private function isHttpsRequest(): bool
    {
        if (!empty($this->request->server('HTTPS')) && strtolower((string) $this->request->server('HTTPS')) !== 'off') {
            return true;
        }

        return str_starts_with(strtolower((string) ($this->request->server('HTTP_X_FORWARDED_PROTO') ?? '')), 'https');
    }
}
