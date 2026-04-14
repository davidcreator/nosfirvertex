<?php
declare(strict_types=1);

namespace NosfirVertex\System\Engine;

use NosfirVertex\System\Library\Auth;
use NosfirVertex\System\Library\Config;
use NosfirVertex\System\Library\Csrf;
use NosfirVertex\System\Library\Database;
use NosfirVertex\System\Library\Logger;
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

        if (in_array($this->area, ['catalog', 'admin'], true) && !is_file($installedConfigFile)) {
            $response->redirect(base_url('install/index.php'));
        }

        $db = $this->buildDatabase($config);
        $logger = new Logger(DIR_STORAGE . '/logs/app.log', $db);
        $view = new View(DIR_ROOT . '/' . $this->area . '/view');

        $authRole = $this->area === 'admin' ? 'admin' : 'user';
        $auth = new Auth($session, $db, $authRole);

        $registry->set('config', $config);
        $registry->set('request', $request);
        $registry->set('response', $response);
        $registry->set('session', $session);
        $registry->set('csrf', $csrf);
        $registry->set('db', $db);
        $registry->set('logger', $logger);
        $registry->set('view', $view);
        $registry->set('auth', $auth);

        $routes = $config->get('routes', []);
        $router = new Router($routes);

        try {
            $match = $router->dispatch($request->getPath());

            if ($match === null) {
                $fallbackAction = $config->get('routes.404');
                if ($fallbackAction === null) {
                    throw new \RuntimeException('Route not found.');
                }

                $match = ['action' => $fallbackAction, 'params' => []];
            }

            $this->runAction($registry, (string) $match['action'], $match['params']);
        } catch (\Throwable $exception) {
            $logger->error('Unhandled exception', [
                'context' => $this->area,
                'message' => $exception->getMessage(),
            ]);

            $response->setOutput('<h1>Erro interno</h1><p>Ocorreu um erro e foi registrado para análise.</p>');
        }

        $response->send();
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
}
