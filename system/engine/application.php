<?php
declare(strict_types=1);

namespace NosfirVertex\System\Engine;

use NosfirVertex\System\Library\Auth;
use NosfirVertex\System\Library\Config;
use NosfirVertex\System\Library\Csrf;
use NosfirVertex\System\Library\Database;
use NosfirVertex\System\Library\Logger;
use NosfirVertex\System\Library\Language;
use NosfirVertex\System\Library\Request;
use NosfirVertex\System\Library\Response;
use NosfirVertex\System\Library\Session;
use NosfirVertex\System\Library\View;

class Application
{
    public function __construct(private readonly string $area)
    {
    }

    public function run(): void
    {
        $registry = new Registry();

        $config = new Config();
        $config->load(DIR_SYSTEM . '/config/default.php');
        $config->load(DIR_ROOT . '/' . $this->area . '/config.php');

        $installedConfigFile = DIR_SYSTEM . '/config/installed.php';
        if (is_file($installedConfigFile)) {
            $config->load($installedConfigFile);
        }

        $request = new Request();
        $response = new Response();
        $session = new Session(DIR_STORAGE . '/sessions');
        $csrf = new Csrf($session);
        $language = $this->buildLanguage($request, $session, $config);
        $requestId = $this->generateRequestId();

        $response->addHeader('X-Request-Id: ' . $requestId);

        if (in_array($this->area, ['catalog', 'admin'], true) && !is_file($installedConfigFile)) {
            $response->redirect(base_url('install/index.php'));
        }

        $db = $this->buildDatabase($config);
        $logger = new Logger(DIR_STORAGE . '/logs/app.log', $db, $requestId);
        $view = new View(DIR_ROOT . '/' . $this->area . '/view');

        $authRole = $this->area === 'admin' ? 'admin' : 'user';
        $auth = new Auth($session, $db, $authRole);

        $registry->set('config', $config);
        $registry->set('request', $request);
        $registry->set('response', $response);
        $registry->set('session', $session);
        $registry->set('csrf', $csrf);
        $registry->set('language', $language);
        $registry->set('db', $db);
        $registry->set('logger', $logger);
        $registry->set('view', $view);
        $registry->set('auth', $auth);
        $registry->set('request_id', $requestId);

        $routes = $config->get('routes', []);
        $router = new Router($routes);

        try {
            $match = $router->dispatch($request->getPath());

            if ($match === null) {
                $fallbackAction = $routes[404] ?? $routes['404'] ?? $config->get('routes.not_found');
                if ($fallbackAction === null) {
                    throw new \RuntimeException('Route not found.');
                }

                $match = ['action' => $fallbackAction, 'params' => []];
            }

            $this->runAction($registry, (string) $match['action'], $match['params']);
        } catch (\Throwable $exception) {
            $logger->error('Unhandled exception', [
                'context' => $this->area,
                'path' => $request->getPath(),
                'message' => $exception->getMessage(),
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $response->addHeader('HTTP/1.1 500 Internal Server Error');
            $response->addHeader('Cache-Control: no-store, no-cache, must-revalidate');
            $response->setOutput($this->renderInternalErrorPage($requestId));
        }

        $response->send();
    }

    private function buildLanguage(Request $request, Session $session, Config $config): Language
    {
        $languageDirectory = DIR_ROOT . '/' . $this->area . '/language';
        $availableLocales = Language::discoverLocales($languageDirectory);

        $fallbackLocale = strtolower(trim((string) $config->get('app.locale', 'pt-br')));
        if ($fallbackLocale === '') {
            $fallbackLocale = 'pt-br';
        }

        if ($availableLocales !== [] && !in_array($fallbackLocale, $availableLocales, true)) {
            $fallbackLocale = $availableLocales[0];
        }

        $requestedLocale = strtolower(trim((string) $request->get('lang', '')));
        $sessionLocale = strtolower(trim((string) $session->get('locale', '')));

        $candidate = $requestedLocale !== '' ? $requestedLocale : $sessionLocale;
        if ($candidate === '') {
            $candidate = $fallbackLocale;
        }

        $language = new Language($languageDirectory, $candidate, $fallbackLocale);

        $resolvedLocale = $language->getLocale();
        $session->set('locale', $resolvedLocale);
        $config->set('app.locale', $resolvedLocale);
        $config->set('app.available_locales', $language->getAvailableLocales());

        nv_set_language($language);

        return $language;
    }

    private function buildDatabase(Config $config): Database|null
    {
        $host = (string) $config->get('database.host', '');
        $name = (string) $config->get('database.name', '');
        $user = (string) $config->get('database.user', '');

        if ($host === '' || $name === '' || $user === '') {
            return null;
        }

        try {
            return new Database(
                $host,
                (int) $config->get('database.port', 3306),
                $name,
                $user,
                (string) $config->get('database.password', '')
            );
        } catch (\Throwable) {
            return null;
        }
    }

    private function runAction(Registry $registry, string $action, array $params): void
    {
        if (!str_contains($action, '@')) {
            throw new \RuntimeException('Invalid action: ' . $action);
        }

        [$className, $method] = explode('@', $action, 2);

        if (!class_exists($className)) {
            throw new \RuntimeException('Controller class not found: ' . $className);
        }

        $controller = new $className($registry);

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException('Controller method not found: ' . $action);
        }

        $result = $controller->{$method}(...$params);

        if (is_string($result)) {
            $registry->get('response')->setOutput($result);
        }
    }

    private function generateRequestId(): string
    {
        try {
            return dechex(time()) . '-' . bin2hex(random_bytes(8));
        } catch (\Throwable) {
            return dechex(time()) . '-' . dechex(mt_rand());
        }
    }

    private function renderInternalErrorPage(string $requestId): string
    {
        $backUrl = match ($this->area) {
            'admin' => base_url('admin/index.php?route=dashboard'),
            'install' => base_url('install/index.php'),
            default => base_url('catalog/index.php'),
        };

        $backLabel = match ($this->area) {
            'admin' => lang('Voltar ao admin'),
            'install' => lang('Voltar ao instalador'),
            default => lang('Voltar ao início'),
        };

        $title = lang('Erro interno');
        $description = lang('Ocorreu um erro inesperado. Nossa equipe pode localizar o evento com o identificador abaixo.');
        $htmlLang = html_lang();
        $safeRequestId = htmlspecialchars($requestId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeBackUrl = htmlspecialchars($backUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeBackLabel = htmlspecialchars($backLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDescription = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeHtmlLang = htmlspecialchars($htmlLang, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return '<!doctype html>'
            . '<html lang="' . $safeHtmlLang . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $safeTitle . '</title>'
            . '<style>'
            . 'body{margin:0;font-family:Segoe UI,Tahoma,sans-serif;background:#f4f7fb;color:#1b2d3a;}'
            . '.wrap{max-width:680px;margin:48px auto;padding:0 16px;}'
            . '.card{background:#fff;border:1px solid #d6e1e8;border-radius:12px;padding:22px;box-shadow:0 12px 24px rgba(27,45,58,.08);}'
            . 'h1{margin:0 0 10px;font-size:26px;}p{margin:0 0 10px;line-height:1.5;}'
            . '.muted{color:#5f7280;font-size:13px;word-break:break-word;}'
            . 'a.btn{display:inline-block;margin-top:8px;background:#0e7c7b;color:#fff;text-decoration:none;padding:10px 14px;border-radius:8px;}'
            . '</style></head><body>'
            . '<div class="wrap"><div class="card">'
            . '<h1>' . $safeTitle . '</h1>'
            . '<p>' . $safeDescription . '</p>'
            . '<p class="muted">Request ID: <strong>' . $safeRequestId . '</strong></p>'
            . '<a class="btn" href="' . $safeBackUrl . '">' . $safeBackLabel . '</a>'
            . '</div></div></body></html>';
    }
}
