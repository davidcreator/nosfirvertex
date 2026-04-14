<?php
declare(strict_types=1);

if (!defined('DIR_ROOT')) {
    define('DIR_ROOT', dirname(__DIR__));
    define('DIR_SYSTEM', DIR_ROOT . '/system');
    define('DIR_STORAGE', DIR_SYSTEM . '/storage');
}

ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

require_once DIR_SYSTEM . '/helper/common.php';

$vendorAutoload = DIR_SYSTEM . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'AureaVertex\\System\\' => DIR_SYSTEM . '/',
        'AureaVertex\\Catalog\\' => DIR_ROOT . '/catalog/',
        'AureaVertex\\Admin\\' => DIR_ROOT . '/admin/',
        'AureaVertex\\Install\\' => DIR_ROOT . '/install/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . strtolower(str_replace('\\', '/', $relative)) . '.php';

        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});
