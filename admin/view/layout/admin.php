<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($app_name ?? 'AureaVertex Admin') ?></title>
    <link rel="stylesheet" href="<?= e(base_url('admin/css/fonts/fontawesome/css/all.min.css')) ?>">
    <style>
        :root {
            --bg: #f3f6fa;
            --surface: #ffffff;
            --text: #1c2a39;
            --muted: #5f6f82;
            --primary: #0f5b9e;
            --border: #d7e0ea;
            --success: #0f8b55;
            --danger: #b63838;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(160deg, #f3f7fc 0%, #eaf2fb 45%, #f8fbff 100%);
            color: var(--text);
        }
        .shell { max-width: 1200px; margin: 0 auto; padding: 14px; }
        .nav {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .menu { display: flex; gap: 8px; flex-wrap: wrap; }
        .menu a {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 10px;
            text-decoration: none;
            color: var(--text);
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
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .flash {
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid;
        }
        .success { border-color: var(--success); background: #e8f7ef; }
        .error { border-color: var(--danger); background: #fdecec; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid var(--border); padding: 10px; text-align: left; font-size: 14px; }
        .grid { display: grid; gap: 10px; }
        @media (min-width: 768px) { .grid.cols-3 { grid-template-columns: repeat(3, 1fr); } }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, textarea, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 10px;
            margin-bottom: 10px;
        }
        textarea { min-height: 90px; resize: vertical; }
        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
        }
        .muted { color: var(--muted); }
    </style>
</head>
<body>
<div class="shell">
    <nav class="nav">
        <strong><a href="<?= e(base_url('admin/index.php?route=dashboard')) ?>" style="text-decoration:none;color:var(--text);">AureaVertex Admin</a></strong>
        <div class="menu">
            <?php if (!empty($auth_user)): ?>
                <a href="<?= e(base_url('admin/index.php?route=dashboard')) ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="<?= e(base_url('admin/index.php?route=users')) ?>"><i class="fa-solid fa-users"></i> Usuários</a>
                <a href="<?= e(base_url('admin/index.php?route=resumes')) ?>"><i class="fa-solid fa-file-lines"></i> Currículos</a>
                <a href="<?= e(base_url('admin/index.php?route=templates')) ?>"><i class="fa-solid fa-layer-group"></i> Templates</a>
                <a href="<?= e(base_url('admin/index.php?route=ads')) ?>"><i class="fa-solid fa-rectangle-ad"></i> Anúncios</a>
                <a href="<?= e(base_url('admin/index.php?route=settings')) ?>"><i class="fa-solid fa-gears"></i> Configurações</a>
                <a href="<?= e(base_url('admin/index.php?route=logs')) ?>"><i class="fa-solid fa-list-check"></i> Logs</a>
                <a class="primary" href="<?= e(base_url('admin/index.php?route=logout')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (!empty($flash_success)): ?>
        <div class="flash success"><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="flash error"><?= e($flash_error) ?></div>
    <?php endif; ?>

    <?= $content ?>
</div>
<script src="<?= e(base_url('admin/js/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(base_url('admin/js/modal-fix.js')) ?>"></script>
</body>
</html>
