<?php
declare(strict_types=1);

namespace NosfirVertex\System\Engine;

use NosfirVertex\System\Library\Language;

abstract class Controller
{
    public function __construct(protected readonly Registry $registry)
    {
    }

    public function __get(string $name): mixed
    {
        return $this->registry->get($name);
    }

    protected function page(string $template, array $data = [], string|null $layout = null): string
    {
        $layoutToUse = $layout ?? (string) $this->config->get('app.layout', 'layout/main');
        $flashSuccess = $this->session->get('flash_success');
        $flashError = $this->session->get('flash_error');
        $this->session->remove('flash_success');
        $this->session->remove('flash_error');

        $data['app_name'] = $this->config->get('app.name', 'NosfirVertex');
        $data['theme'] = $this->session->get('theme', 'light');
        $data['csrf_token'] = $this->csrf->token();
        $data['auth_user'] = $this->auth->user();
        $data['base_url'] = base_url();
        $data['flash_success'] = is_string($flashSuccess) ? $flashSuccess : '';
        $data['flash_error'] = is_string($flashError) ? $flashError : '';
        $data['current_locale'] = current_locale();
        $data['available_locales'] = available_locales();
        $data['html_lang'] = html_lang();

        return translate_markup($this->view->page($layoutToUse, $template, $data));
    }

    protected function redirect(string $path, int $status = 302): never
    {
        $this->response->redirect(base_url($path), $status);
    }

    protected function validateCsrfToken(): bool
    {
        $token = (string) ($this->request->post('csrf_token') ?? '');

        return $this->csrf->validate($token);
    }

    protected function flash(string $key, string $message): void
    {
        $this->session->set('flash_' . $key, $message);
    }

    protected function lang(string $key, array $replace = [], string $default = ''): string
    {
        if ($this->registry->has('language')) {
            $language = $this->registry->get('language');

            if ($language instanceof Language) {
                return $language->get($key, $replace, $default);
            }
        }

        return lang($key, $replace, $default);
    }
}
