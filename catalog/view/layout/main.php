<!doctype html>
<html lang="pt-BR" data-theme="<?= e($theme ?? 'light') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($app_name ?? 'AureaVertex') ?></title>
    <link rel="stylesheet" href="<?= e(base_url('catalog/view/css/fonts/fontawesome/css/all.min.css')) ?>">
    <style>
        :root {
            --bg: #f4f7fb;
            --bg-accent: linear-gradient(145deg, #f2f7ff, #edf6f5 45%, #f8fbff);
            --surface: #ffffff;
            --text: #1b2d3a;
            --muted: #5f7280;
            --primary: #0e7c7b;
            --secondary: #1f4e79;
            --border: #d6e1e8;
            --success: #0f8b55;
            --danger: #b83434;
            --warning: #9a6100;
            --radius: 14px;
            --shadow: 0 12px 24px rgba(27, 45, 58, 0.08);
            --card-grad: linear-gradient(160deg, #ffffff 0%, #f7fbff 100%);
        }

        html[data-theme="dark"] {
            --bg: #0f1b22;
            --bg-accent: radial-gradient(circle at 20% 20%, #172d39, #0b151b 55%, #071014);
            --surface: #13222c;
            --text: #e5f0f7;
            --muted: #9ab0bf;
            --primary: #2cb8a6;
            --secondary: #78b3f3;
            --border: #29414f;
            --success: #59d58f;
            --danger: #ff8888;
            --warning: #ffce6b;
            --shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
            --card-grad: linear-gradient(160deg, #13242f 0%, #0f1e28 100%);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-accent);
            min-height: 100vh;
        }

        a { color: var(--secondary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .shell {
            max-width: 1100px;
            margin: 0 auto;
            padding: 12px 12px 24px;
        }

        .nav {
            background: var(--card-grad);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .brand {
            font-weight: 700;
            color: var(--text);
            letter-spacing: .3px;
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .menu a, .menu button {
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 11px;
            color: var(--text);
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .menu a.primary {
            background: var(--primary);
            color: #fff;
            border-color: transparent;
        }

        .flash {
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            border: 1px solid;
        }

        .flash.success {
            border-color: color-mix(in srgb, var(--success) 45%, var(--border));
            background: color-mix(in srgb, var(--success) 14%, transparent);
        }

        .flash.error {
            border-color: color-mix(in srgb, var(--danger) 45%, var(--border));
            background: color-mix(in srgb, var(--danger) 14%, transparent);
        }

        .card {
            background: var(--card-grad);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 16px;
            margin-bottom: 12px;
        }

        .grid {
            display: grid;
            gap: 12px;
        }

        .button {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            color: var(--text);
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }

        .button.primary {
            background: var(--primary);
            color: #fff;
            border-color: transparent;
        }

        .button.warn {
            background: #c03f3f;
            color: #fff;
            border-color: transparent;
        }

        input, textarea, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 12px;
            color: var(--text);
            background: color-mix(in srgb, var(--surface) 96%, transparent);
            margin-top: 4px;
            margin-bottom: 10px;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 14px;
        }

        .muted { color: var(--muted); }

        .fa-solid,
        .fa-regular,
        .fa-brands {
            line-height: 1;
        }

        footer {
            margin-top: 16px;
            padding-top: 8px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
        }

        @media (min-width: 768px) {
            .shell {
                padding: 24px 18px 30px;
            }

            .grid.cols-2 {
                grid-template-columns: 1fr 1fr;
            }

            .grid.cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
<div class="shell">
    <nav class="nav">
        <div class="brand"><a href="<?= e(base_url('catalog/index.php')) ?>">AureaVertex</a></div>
        <div class="menu">
            <a href="<?= e(base_url('catalog/index.php')) ?>"><i class="fa-solid fa-house"></i> Início</a>
            <a href="<?= e(base_url('catalog/index.php?route=templates')) ?>"><i class="fa-solid fa-layer-group"></i> Templates</a>
            <a href="<?= e(base_url('catalog/index.php?route=doacoes')) ?>"><i class="fa-solid fa-hand-holding-heart"></i> Doações</a>
            <?php if (!empty($auth_user)): ?>
                <a href="<?= e(base_url('catalog/index.php?route=dashboard')) ?>"><i class="fa-solid fa-table-columns"></i> Painel</a>
                <a href="<?= e(base_url('catalog/index.php?route=resume/create')) ?>" class="primary"><i class="fa-solid fa-file-circle-plus"></i> Novo currículo</a>
                <a href="<?= e(base_url('catalog/index.php?route=account/settings')) ?>"><i class="fa-solid fa-user-gear"></i> Conta</a>
                <a href="<?= e(base_url('catalog/index.php?route=logout')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            <?php else: ?>
                <a href="<?= e(base_url('catalog/index.php?route=login')) ?>"><i class="fa-solid fa-right-to-bracket"></i> Entrar</a>
                <a href="<?= e(base_url('catalog/index.php?route=register')) ?>" class="primary"><i class="fa-solid fa-user-plus"></i> Cadastrar</a>
            <?php endif; ?>
            <a href="<?= e(base_url('catalog/index.php?route=theme/toggle')) ?>"><i class="fa-solid fa-circle-half-stroke"></i> Tema</a>
        </div>
    </nav>

    <?php if (!empty($flash_success)): ?>
        <div class="flash success"><?= e($flash_success) ?></div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="flash error"><?= e($flash_error) ?></div>
    <?php endif; ?>

    <?= $content ?>

    <footer>
        AureaVertex • Plataforma gratuita para criação e exportação de currículos.
    </footer>
</div>
<script src="<?= e(base_url('catalog/view/js/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(base_url('catalog/view/js/modal-fix.js')) ?>"></script>
</body>
</html>

