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
            $hasContentTypeHeader = false;

            foreach ($this->headers as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $hasContentTypeHeader = true;
                }
                header($header);
            }

            if (!$hasContentTypeHeader) {
                header('Content-Type: text/html; charset=utf-8');
            }
        }

        echo $this->output;
    }
}
