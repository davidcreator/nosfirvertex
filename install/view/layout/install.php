<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($app_name ?? 'Instalador NosfirVertex') ?></title>
    <style>
        :root {
            --bg: #f4f6f8;
            --surface: #ffffff;
            --text: #1d2a3a;
            --muted: #637182;
            --primary: #1769aa;
            --border: #d8e0e8;
            --success: #0d7f4f;
            --danger: #b22a2a;
            --warning: #a56b07;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: linear-gradient(160deg, #f2f5f9 0%, #e6edf6 45%, #f8fbff 100%);
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 20px 16px 40px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 6px 16px rgba(16, 34, 58, 0.06);
        }
        h1, h2, h3 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; }
        .ok { color: var(--success); font-weight: 600; }
        .fail { color: var(--danger); font-weight: 600; }
        .warn { color: var(--warning); font-weight: 600; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            margin-bottom: 12px;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0 12px;
        }
        .checkbox-row input {
            width: auto;
            margin: 0;
        }
        button, .button {
            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .button-secondary {
            background: #445b74;
        }
        .grid {
            display: grid;
            gap: 12px;
        }
        .stepper {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }
        .step-chip {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
            background: #f8fbff;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }
        .step-chip.active {
            background: #eaf3ff;
            color: var(--text);
            border-color: #b7d3ef;
            font-weight: 700;
        }
        .flash {
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid;
        }
        .flash.success {
            color: #0d7f4f;
            border-color: #8bc8ab;
            background: #ecf9f1;
        }
        .flash.error {
            color: #b22a2a;
            border-color: #e5b4b4;
            background: #fff2f2;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .details {
            background: #f7faff;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
        }
        @media (min-width: 768px) {
            .grid.two {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="stepper">
        <div class="step-chip <?= (int) ($step ?? 0) === 1 ? 'active' : '' ?>">Passo 1<br><small>Requisitos</small></div>
        <div class="step-chip <?= (int) ($step ?? 0) === 2 ? 'active' : '' ?>">Passo 2<br><small>Banco de dados</small></div>
        <div class="step-chip <?= (int) ($step ?? 0) === 3 ? 'active' : '' ?>">Passo 3<br><small>Administrador</small></div>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="flash success"><?= e($flash_success) ?></div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="flash error"><?= e($flash_error) ?></div>
    <?php endif; ?>

    <?= $content ?>
</div>
</body>
</html>
