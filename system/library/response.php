<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Response
{
    private array $headers = [];
    private string $output = '';

    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    public function redirect(string $url, int $status = 302): never
    {
        if (!headers_sent()) {
            header('Location: ' . $url, true, $status);
        }

        exit;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header);
            }

            if (!$this->hasHeaderPrefix('Content-Type:')) {
                header('Content-Type: text/html; charset=utf-8');
            }

            // Security headers with compatibility for current inline templates.
            if (!$this->hasHeaderPrefix('X-Content-Type-Options:')) {
                header('X-Content-Type-Options: nosniff');
            }

            if (!$this->hasHeaderPrefix('X-Frame-Options:')) {
                header('X-Frame-Options: DENY');
            }

            if (!$this->hasHeaderPrefix('Referrer-Policy:')) {
                header('Referrer-Policy: strict-origin-when-cross-origin');
            }

            if (!$this->hasHeaderPrefix('Permissions-Policy:')) {
                header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
            }

            if (!$this->hasHeaderPrefix('Content-Security-Policy:')) {
                header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'; form-action 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: http: https:; font-src 'self' data:");
            }
        }

        echo $this->output;
    }

    private function hasHeaderPrefix(string $prefix): bool
    {
        foreach ($this->headers as $header) {
            if (stripos($header, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
