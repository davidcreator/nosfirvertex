<?php
declare(strict_types=1);

/**
 * Smoke test HTTP de rotas e fluxos CSRF.
 *
 * Uso:
 *   php tests/smoke/routes_smoke.php http://127.0.0.1:8080
 */

$baseUrl = $argv[1] ?? getenv('NV_SMOKE_BASE_URL') ?: 'http://127.0.0.1:8080';
$baseUrl = rtrim((string) $baseUrl, '/');

$fatalMarkers = [
    'Fatal error',
    'Parse error',
    'Uncaught ',
];

$failures = [];

fwrite(STDOUT, 'Base URL: ' . $baseUrl . PHP_EOL . PHP_EOL);

$basicRoutes = [
    '/',
    '/index.php',
    '/install/index.php',
    '/install/index.php?route=step/1',
    '/catalog/index.php',
    '/catalog/index.php?route=templates',
    '/admin/index.php',
    '/catalog/index.php?route=rota-inexistente',
    '/admin/index.php?route=rota-inexistente',
];

runBasicGetSmoke($baseUrl, $basicRoutes, $fatalMarkers, $failures);
runPostCsrfSmoke($baseUrl, $fatalMarkers, $failures);

if ($failures !== []) {
    fwrite(STDERR, PHP_EOL . 'Falhas de smoke:' . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, '- ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, PHP_EOL . 'Smoke de rotas concluido com sucesso.' . PHP_EOL);
exit(0);

function runBasicGetSmoke(string $baseUrl, array $routes, array $fatalMarkers, array &$failures): void
{
    fwrite(STDOUT, '## GET basico' . PHP_EOL);
    $cookies = [];

    foreach ($routes as $route) {
        $result = request($baseUrl . $route, 'GET', [], [], $cookies);
        printResult('GET ' . $route, $result);

        if ($result['ok'] !== true) {
            $failures[] = $route . ': requisicao falhou.';
            continue;
        }

        if (($result['status'] ?? 0) >= 500) {
            $failures[] = $route . ': status HTTP ' . (int) $result['status'] . '.';
            continue;
        }

        assertNoFatalMarkers($route, (string) $result['body'], $fatalMarkers, $failures);
    }

    fwrite(STDOUT, PHP_EOL);
}

function runPostCsrfSmoke(string $baseUrl, array $fatalMarkers, array &$failures): void
{
    fwrite(STDOUT, '## POST/CSRF' . PHP_EOL);

    $probeCookies = [];
    $probe = request($baseUrl . '/catalog/index.php?route=login', 'GET', [], [], $probeCookies);
    printResult('GET /catalog/index.php?route=login (probe)', $probe);

    if ($probe['ok'] !== true) {
        $failures[] = 'probe catalog/login: requisicao falhou.';
        return;
    }

    if (isInstallRedirect($probe)) {
        fwrite(STDOUT, 'SKIP: ambiente em modo instalador (catalog/admin redirecionam para install).' . PHP_EOL . PHP_EOL);
        return;
    }

    runCatalogCsrfFlows($baseUrl, $fatalMarkers, $failures);
    runAdminCsrfFlows($baseUrl, $fatalMarkers, $failures);
    fwrite(STDOUT, PHP_EOL);
}

function runCatalogCsrfFlows(string $baseUrl, array $fatalMarkers, array &$failures): void
{
    fwrite(STDOUT, 'Catalog:' . PHP_EOL);

    $cookies = [];
    $loginPage = request($baseUrl . '/catalog/index.php?route=login', 'GET', [], [], $cookies);
    printResult('GET /catalog/index.php?route=login', $loginPage);
    assertHealthyResponse('catalog login page', $loginPage, $fatalMarkers, $failures);

    $loginToken = extractCsrfToken((string) ($loginPage['body'] ?? ''));
    if (!is_string($loginToken) || $loginToken === '') {
        $failures[] = 'catalog login page: token CSRF nao encontrado.';
        return;
    }

    $invalidLogin = request(
        $baseUrl . '/catalog/index.php?route=login',
        'POST',
        [
            'email' => 'smoke@example.test',
            'password' => 'invalid-password',
            'csrf_token' => 'invalid-token',
        ],
        [],
        $cookies
    );
    printResult('POST /catalog/index.php?route=login (csrf invalido)', $invalidLogin);
    assertStatusIn('catalog login csrf invalido', $invalidLogin, [200], $failures);
    assertNoFatalMarkers('catalog login csrf invalido', (string) $invalidLogin['body'], $fatalMarkers, $failures);

    $validLogin = request(
        $baseUrl . '/catalog/index.php?route=login',
        'POST',
        [
            'email' => 'smoke@example.test',
            'password' => 'invalid-password',
            'csrf_token' => $loginToken,
        ],
        [],
        $cookies
    );
    printResult('POST /catalog/index.php?route=login (csrf valido)', $validLogin);
    assertStatusIn('catalog login csrf valido', $validLogin, [200], $failures);
    assertNoFatalMarkers('catalog login csrf valido', (string) $validLogin['body'], $fatalMarkers, $failures);

    $homePage = request($baseUrl . '/catalog/index.php', 'GET', [], [], $cookies);
    printResult('GET /catalog/index.php', $homePage);
    assertHealthyResponse('catalog home page', $homePage, $fatalMarkers, $failures);

    $homeToken = extractCsrfToken((string) ($homePage['body'] ?? ''));
    if (!is_string($homeToken) || $homeToken === '') {
        $failures[] = 'catalog home page: token CSRF nao encontrado.';
        return;
    }

    $toggleInvalid = request(
        $baseUrl . '/catalog/index.php?route=theme/toggle',
        'POST',
        ['csrf_token' => 'invalid-token'],
        ['Referer: ' . $baseUrl . '/catalog/index.php'],
        $cookies
    );
    printResult('POST /catalog/index.php?route=theme/toggle (csrf invalido)', $toggleInvalid);
    assertStatusIn('catalog theme toggle csrf invalido', $toggleInvalid, [302, 303], $failures);

    $toggleValid = request(
        $baseUrl . '/catalog/index.php?route=theme/toggle',
        'POST',
        ['csrf_token' => $homeToken],
        ['Referer: ' . $baseUrl . '/catalog/index.php?route=templates'],
        $cookies
    );
    printResult('POST /catalog/index.php?route=theme/toggle (csrf valido)', $toggleValid);
    assertStatusIn('catalog theme toggle csrf valido', $toggleValid, [302, 303], $failures);

    $logoutInvalid = request(
        $baseUrl . '/catalog/index.php?route=logout',
        'POST',
        ['csrf_token' => 'invalid-token'],
        [],
        $cookies
    );
    printResult('POST /catalog/index.php?route=logout (csrf invalido)', $logoutInvalid);
    assertStatusIn('catalog logout csrf invalido', $logoutInvalid, [302, 303], $failures);

    $logoutValid = request(
        $baseUrl . '/catalog/index.php?route=logout',
        'POST',
        ['csrf_token' => $homeToken],
        [],
        $cookies
    );
    printResult('POST /catalog/index.php?route=logout (csrf valido)', $logoutValid);
    assertStatusIn('catalog logout csrf valido', $logoutValid, [302, 303], $failures);
}

function runAdminCsrfFlows(string $baseUrl, array $fatalMarkers, array &$failures): void
{
    fwrite(STDOUT, 'Admin:' . PHP_EOL);

    $cookies = [];
    $loginPage = request($baseUrl . '/admin/index.php?route=login', 'GET', [], [], $cookies);
    printResult('GET /admin/index.php?route=login', $loginPage);
    assertHealthyResponse('admin login page', $loginPage, $fatalMarkers, $failures);

    $loginToken = extractCsrfToken((string) ($loginPage['body'] ?? ''));
    if (!is_string($loginToken) || $loginToken === '') {
        $failures[] = 'admin login page: token CSRF nao encontrado.';
        return;
    }

    $invalidLogin = request(
        $baseUrl . '/admin/index.php?route=login',
        'POST',
        [
            'email' => 'admin@example.test',
            'password' => 'invalid-password',
            'csrf_token' => 'invalid-token',
        ],
        [],
        $cookies
    );
    printResult('POST /admin/index.php?route=login (csrf invalido)', $invalidLogin);
    assertStatusIn('admin login csrf invalido', $invalidLogin, [200], $failures);
    assertNoFatalMarkers('admin login csrf invalido', (string) $invalidLogin['body'], $fatalMarkers, $failures);

    $validLogin = request(
        $baseUrl . '/admin/index.php?route=login',
        'POST',
        [
            'email' => 'admin@example.test',
            'password' => 'invalid-password',
            'csrf_token' => $loginToken,
        ],
        [],
        $cookies
    );
    printResult('POST /admin/index.php?route=login (csrf valido)', $validLogin);
    assertStatusIn('admin login csrf valido', $validLogin, [200], $failures);
    assertNoFatalMarkers('admin login csrf valido', (string) $validLogin['body'], $fatalMarkers, $failures);

    $logoutInvalid = request(
        $baseUrl . '/admin/index.php?route=logout',
        'POST',
        ['csrf_token' => 'invalid-token'],
        [],
        $cookies
    );
    printResult('POST /admin/index.php?route=logout (csrf invalido)', $logoutInvalid);
    assertStatusIn('admin logout csrf invalido', $logoutInvalid, [302, 303], $failures);

    $logoutValid = request(
        $baseUrl . '/admin/index.php?route=logout',
        'POST',
        ['csrf_token' => $loginToken],
        [],
        $cookies
    );
    printResult('POST /admin/index.php?route=logout (csrf valido)', $logoutValid);
    assertStatusIn('admin logout csrf valido', $logoutValid, [302, 303], $failures);
}

function request(string $url, string $method, array $formData, array $extraHeaders, array &$cookies): array
{
    $method = strtoupper($method);
    $headers = [
        'User-Agent: NosfirVertex-Smoke/1.1',
        'Connection: close',
    ];

    if ($cookies !== []) {
        $cookieParts = [];
        foreach ($cookies as $name => $value) {
            $cookieParts[] = $name . '=' . $value;
        }
        $headers[] = 'Cookie: ' . implode('; ', $cookieParts);
    }

    foreach ($extraHeaders as $header) {
        $headers[] = $header;
    }

    $content = '';
    if ($method === 'POST') {
        $content = http_build_query($formData);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Content-Length: ' . (string) strlen($content);
    }

    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'timeout' => 20,
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
            'protocol_version' => 1.1,
            'header' => implode("\r\n", $headers),
            'content' => $content,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];

    applyCookiesFromHeaders($responseHeaders, $cookies);

    return [
        'ok' => is_string($body),
        'status' => extractStatusCode($responseHeaders),
        'location' => extractHeader($responseHeaders, 'Location'),
        'body' => is_string($body) ? $body : '',
    ];
}

function applyCookiesFromHeaders(array $headers, array &$cookies): void
{
    foreach ($headers as $header) {
        if (!is_string($header) || stripos($header, 'Set-Cookie:') !== 0) {
            continue;
        }

        $cookieLine = trim(substr($header, strlen('Set-Cookie:')));
        $pair = explode(';', $cookieLine, 2)[0] ?? '';
        if (!str_contains($pair, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $pair, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name !== '') {
            $cookies[$name] = $value;
        }
    }
}

function extractStatusCode(array $headers): int
{
    $status = 0;

    foreach ($headers as $header) {
        if (!is_string($header)) {
            continue;
        }

        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches) !== 1) {
            continue;
        }

        $status = (int) $matches[1];
    }

    return $status;
}

function extractHeader(array $headers, string $name): string
{
    $needle = strtolower($name) . ':';
    $value = '';

    foreach ($headers as $header) {
        if (!is_string($header)) {
            continue;
        }

        if (str_starts_with(strtolower($header), $needle)) {
            $value = trim(substr($header, strlen($needle)));
        }
    }

    return $value;
}

function printResult(string $label, array $result): void
{
    $status = (int) ($result['status'] ?? 0);
    $location = (string) ($result['location'] ?? '');
    $line = '[' . ($status > 0 ? (string) $status : 'ERR') . '] ' . $label;

    if ($location !== '') {
        $line .= ' -> ' . $location;
    }

    fwrite(STDOUT, $line . PHP_EOL);
}

function assertHealthyResponse(string $label, array $result, array $fatalMarkers, array &$failures): void
{
    if ($result['ok'] !== true) {
        $failures[] = $label . ': requisicao falhou.';
        return;
    }

    if ((int) ($result['status'] ?? 0) >= 500) {
        $failures[] = $label . ': status HTTP ' . (int) $result['status'] . '.';
        return;
    }

    assertNoFatalMarkers($label, (string) ($result['body'] ?? ''), $fatalMarkers, $failures);
}

function assertStatusIn(string $label, array $result, array $expected, array &$failures): void
{
    if ($result['ok'] !== true) {
        $failures[] = $label . ': requisicao falhou.';
        return;
    }

    $status = (int) ($result['status'] ?? 0);
    if (!in_array($status, $expected, true)) {
        $failures[] = $label . ': status HTTP ' . $status . ' (esperado: ' . implode(',', $expected) . ').';
    }
}

function assertNoFatalMarkers(string $label, string $body, array $markers, array &$failures): void
{
    foreach ($markers as $marker) {
        if (stripos($body, $marker) === false) {
            continue;
        }

        $failures[] = $label . ': encontrou marcador de erro fatal "' . $marker . '".';
        return;
    }
}

function extractCsrfToken(string $html): string|null
{
    $patterns = [
        '/name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/i',
        '/value=["\']([^"\']+)["\']\s+name=["\']csrf_token["\']/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches) !== 1) {
            continue;
        }

        $token = html_entity_decode((string) $matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if ($token !== '') {
            return $token;
        }
    }

    return null;
}

function isInstallRedirect(array $result): bool
{
    $status = (int) ($result['status'] ?? 0);
    $location = strtolower((string) ($result['location'] ?? ''));

    if (!in_array($status, [301, 302, 303, 307, 308], true)) {
        return false;
    }

    if ($location === '') {
        return false;
    }

    return str_contains($location, '/install/index.php');
}
