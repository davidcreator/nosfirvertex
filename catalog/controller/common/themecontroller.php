<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Common;

use NosfirVertex\System\Engine\Controller;

class ThemeController extends Controller
{
    public function toggle(): never
    {
        if (!$this->request->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', $this->lang('Requisição inválida para troca de tema.'));
            $this->redirect('catalog/index.php');
        }

        $current = $this->session->get('theme', 'light');
        $next = $current === 'dark' ? 'light' : 'dark';

        $this->session->set('theme', $next);

        $referer = (string) ($this->request->server('HTTP_REFERER') ?? '');
        if ($this->isSafeReferer($referer)) {
            $this->response->redirect($referer);
        }

        $this->redirect('catalog/index.php');
    }

    private function isSafeReferer(string $referer): bool
    {
        if ($referer === '') {
            return false;
        }

        $target = parse_url($referer);
        if (!is_array($target)) {
            return false;
        }

        $requestScheme = $this->isHttpsRequest() ? 'https' : 'http';
        $requestHost = strtolower((string) ($this->request->server('HTTP_HOST') ?? ''));
        $targetHost = strtolower((string) ($target['host'] ?? ''));
        $targetScheme = strtolower((string) ($target['scheme'] ?? ''));

        return $targetHost !== ''
            && $targetScheme !== ''
            && $targetHost === $requestHost
            && $targetScheme === $requestScheme;
    }

    private function isHttpsRequest(): bool
    {
        if (!empty($this->request->server('HTTPS')) && strtolower((string) $this->request->server('HTTPS')) !== 'off') {
            return true;
        }

        return str_starts_with(strtolower((string) ($this->request->server('HTTP_X_FORWARDED_PROTO') ?? '')), 'https');
    }
}
