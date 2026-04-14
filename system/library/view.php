<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class View
{
    public function __construct(private readonly string $baseDirectory)
    {
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->baseDirectory . '/' . trim($template, '/') . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException('Template not found: ' . $file);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;

        return (string) ob_get_clean();
    }

    public function page(string $layout, string $template, array $data = []): string
    {
        $content = $this->render($template, $data);
        $data['content'] = $content;

        return $this->render($layout, $data);
    }
}
