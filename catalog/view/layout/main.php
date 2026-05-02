<!doctype html>
<html lang="<?= e($html_lang ?? html_lang()) ?>" data-theme="<?= e($theme ?? 'light') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $metaTitle = (string) ($app_name ?? 'Vertex');
    $metaDescription = 'Vertex is a free platform for creating and exporting resumes.';
    $metaImage = base_url('image/vertex_og.png');
    $metaUrl = base_url('catalog/index.php');
    ?>
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
    <link rel="stylesheet" href="<?= e(base_url('catalog/view/css/fonts/fontawesome/css/all.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('catalog/view/css/layout-main.css')) ?>">
</head>
<body>
<?php
$localeOptions = is_array($available_locales ?? null) ? $available_locales : available_locales();
$activeLocale = (string) ($current_locale ?? current_locale());
?>
<div class="shell">
    <?php if (empty($hide_nav)): ?>
    <nav class="nav">
        <div class="brand">
            <a href="<?= e(base_url('catalog/index.php')) ?>">
                <img src="<?= e(base_url('image/vertex_logo.png')) ?>" alt="Vertex">
                <span>Vertex</span>
            </a>
        </div>
        <div class="menu">
            <?php foreach ($localeOptions as $localeCode): ?>
                <a href="<?= e(locale_switch_url((string) $localeCode)) ?>"<?= $activeLocale === (string) $localeCode ? ' class="primary"' : '' ?>><?= e(strtoupper((string) $localeCode)) ?></a>
            <?php endforeach; ?>
            <a href="<?= e(base_url('catalog/index.php')) ?>"><i class="fa-solid fa-house"></i> <?= e(lang('Início')) ?></a>
            <a href="<?= e(base_url('catalog/index.php?route=templates')) ?>"><i class="fa-solid fa-layer-group"></i> <?= e(lang('Templates')) ?></a>
            <a href="<?= e(base_url('catalog/index.php?route=doacoes')) ?>"><i class="fa-solid fa-hand-holding-heart"></i> <?= e(lang('Doações')) ?></a>
            <?php if (!empty($auth_user)): ?>
                <a href="<?= e(base_url('catalog/index.php?route=dashboard')) ?>"><i class="fa-solid fa-table-columns"></i> <?= e(lang('Painel')) ?></a>
                <a href="<?= e(base_url('catalog/index.php?route=resume/create')) ?>" class="primary"><i class="fa-solid fa-file-circle-plus"></i> <?= e(lang('Novo currículo')) ?></a>
                <a href="<?= e(base_url('catalog/index.php?route=account/settings')) ?>"><i class="fa-solid fa-user-gear"></i> <?= e(lang('Conta')) ?></a>
                <form method="post" action="<?= e(base_url('catalog/index.php?route=logout')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> <?= e(lang('Sair')) ?></button>
                </form>
            <?php else: ?>
                <a href="<?= e(base_url('catalog/index.php?route=login')) ?>"><i class="fa-solid fa-right-to-bracket"></i> <?= e(lang('Entrar')) ?></a>
                <a href="<?= e(base_url('catalog/index.php?route=register')) ?>" class="primary"><i class="fa-solid fa-user-plus"></i> <?= e(lang('Cadastrar')) ?></a>
            <?php endif; ?>
            <form method="post" action="<?= e(base_url('catalog/index.php?route=theme/toggle')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <button type="submit"><i class="fa-solid fa-circle-half-stroke"></i> <?= e(lang('Tema')) ?></button>
            </form>
        </div>
    </nav>
    <?php endif; ?>

    <?php if (!empty($flash_success)): ?>
        <div class="flash success"><?= e($flash_success) ?></div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="flash error"><?= e($flash_error) ?></div>
    <?php endif; ?>

    <?= $content ?>

    <footer>
        <?= e(lang('Vertex • Plataforma gratuita para criação e exportação de currículos.')) ?>
    </footer>
</div>
<script src="<?= e(base_url('catalog/view/js/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(base_url('catalog/view/js/modal-fix.js')) ?>"></script>
</body>
</html>
