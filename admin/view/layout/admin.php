<?php
$metaTitle = (string) ($app_name ?? lang('Vertex Admin'));
$metaDescription = 'Vertex admin area for managing users, resumes, templates, settings, and logs.';
$metaImage = base_url('image/vertex_og_admin.png');
$metaUrl = base_url('admin/index.php');
?>
<!doctype html>
<html lang="<?= e($html_lang ?? html_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($metaTitle) ?></title>
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($metaUrl) ?>">
    <meta property="og:image" content="<?= e($metaImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">
    <meta name="twitter:image" content="<?= e($metaImage) ?>">
    <link rel="icon" type="image/png" href="<?= e(base_url('image/vertex.png')) ?>">
    <link rel="apple-touch-icon" href="<?= e(base_url('image/vertex.png')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('admin/css/fonts/fontawesome/css/all.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('admin/css/admin-layout.css')) ?>">
</head>
<body class="<?= !empty($auth_user) ? 'admin-auth' : 'admin-guest' ?>">
<?php
$localeOptions = is_array($available_locales ?? null) ? $available_locales : available_locales();
$activeLocale = (string) ($current_locale ?? current_locale());
$currentRoute = trim((string) ($_GET['route'] ?? 'dashboard'));
if ($currentRoute === '') {
    $currentRoute = 'dashboard';
}

$menuItems = [
    ['route' => 'dashboard', 'icon' => 'fa-solid fa-gauge-high', 'label' => lang('Dashboard')],
    ['route' => 'users', 'icon' => 'fa-solid fa-users', 'label' => lang('Usuários')],
    ['route' => 'resumes', 'icon' => 'fa-solid fa-file-lines', 'label' => lang('Currículos')],
    ['route' => 'templates', 'icon' => 'fa-solid fa-layer-group', 'label' => lang('Templates')],
    ['route' => 'ads', 'icon' => 'fa-solid fa-rectangle-ad', 'label' => lang('Anúncios')],
    ['route' => 'settings', 'icon' => 'fa-solid fa-gears', 'label' => lang('Configurações')],
    ['route' => 'logs', 'icon' => 'fa-solid fa-list-check', 'label' => lang('Logs')],
];

$isRouteActive = static function (string $current, string $route): bool {
    return $current === $route || str_starts_with($current, $route . '/');
};

$currentSectionLabel = lang('Dashboard');
foreach ($menuItems as $item) {
    if ($isRouteActive($currentRoute, (string) $item['route'])) {
        $currentSectionLabel = (string) $item['label'];
        break;
    }
}

$userName = trim((string) ($auth_user['full_name'] ?? $auth_user['email'] ?? 'Admin'));
if ($userName === '') {
    $userName = 'Admin';
}
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<div class="admin-shell<?= empty($auth_user) ? ' guest' : '' ?>">
    <?php if (!empty($auth_user)): ?>
        <aside class="sidebar">
            <a class="sidebar-brand" href="<?= e(base_url('admin/index.php?route=dashboard')) ?>">
                <span class="sidebar-brand-logo-wrap">
                    <img class="sidebar-brand-logo" src="<?= e(base_url('image/vertex_logo_wt.png')) ?>" alt="Vertex">
                </span>
                <span>
                    <strong><?= e(lang('Vertex Admin')) ?></strong>
                    <small><?= e(lang('Centro de comando do Vertex')) ?></small>
                </span>
            </a>

            <p class="sidebar-section-label"><?= e(lang('Navegação administrativa')) ?></p>
            <nav class="sidebar-nav">
                <?php foreach ($menuItems as $item): ?>
                    <?php $isActive = $isRouteActive($currentRoute, (string) $item['route']); ?>
                    <a href="<?= e(base_url('admin/index.php?route=' . (string) $item['route'])) ?>"<?= $isActive ? ' class="is-active"' : '' ?>>
                        <span>
                            <i class="<?= e((string) $item['icon']) ?>"></i>
                            <?= e((string) $item['label']) ?>
                        </span>
                        <i class="fa-solid fa-angle-right"></i>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <?= e(lang('Visão consolidada para operação e governança do sistema.')) ?>
            </div>
        </aside>
    <?php endif; ?>

    <div class="main-content-wrapper<?= empty($auth_user) ? ' auth-only' : '' ?>">
        <?php if (!empty($auth_user)): ?>
            <header class="topbar">
                <div class="topbar-title">
                    <strong><?= e($currentSectionLabel) ?></strong>
                    <small><?= e(lang('Gerencie usuários, currículos e configurações em um único painel.')) ?></small>
                </div>

                <div class="topbar-tools">
                    <?php foreach ($localeOptions as $localeCode): ?>
                        <?php $isActiveLocale = $activeLocale === (string) $localeCode; ?>
                        <a href="<?= e(locale_switch_url((string) $localeCode)) ?>"<?= $isActiveLocale ? ' class="is-active"' : '' ?>><?= e(strtoupper((string) $localeCode)) ?></a>
                    <?php endforeach; ?>
                    <a href="<?= e(base_url('catalog/index.php')) ?>" title="<?= e(lang('Abrir área pública')) ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                </div>

                <div class="topbar-user">
                    <span class="user-avatar"><?= e($userInitial) ?></span>
                    <span class="topbar-user-meta">
                        <strong><?= e($userName) ?></strong>
                        <small><?= e((string) ($auth_user['email'] ?? 'admin@localhost')) ?></small>
                    </span>
                    <form class="topbar-logout-form" method="post" action="<?= e(base_url('admin/index.php?route=logout')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                        <button class="topbar-logout-btn" type="submit"><i class="fa-solid fa-right-from-bracket"></i> <?= e(lang('Sair')) ?></button>
                    </form>
                </div>
            </header>
        <?php endif; ?>

        <main class="main-content">
            <?php if (!empty($flash_success)): ?>
                <div class="flash success"><?= e($flash_success) ?></div>
            <?php endif; ?>
            <?php if (!empty($flash_error)): ?>
                <div class="flash error"><?= e($flash_error) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= e(base_url('admin/js/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(base_url('admin/js/modal-fix.js')) ?>"></script>
</body>
</html>
