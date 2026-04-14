<div class="card">
    <h1>Instalador AureaVertex - Passo 3</h1>
    <p>Defina o administrador inicial e conclua a instalação.</p>
</div>

<div class="card">
    <h2>Resumo da conexão com banco</h2>
    <div class="details">
        <p><strong>URL base:</strong> <?= e((string) ($db_form['base_url'] ?? '-')) ?></p>
        <p><strong>Host:</strong> <?= e((string) ($db_form['db_host'] ?? '-')) ?>:<?= e((string) ($db_form['db_port'] ?? '-')) ?></p>
        <p><strong>Banco:</strong> <?= e((string) ($db_form['db_name'] ?? '-')) ?></p>
        <p><strong>Usuário:</strong> <?= e((string) ($db_form['db_user'] ?? '-')) ?></p>
        <p><strong>Status do teste:</strong> <span class="<?= !empty($db_status['success']) ? 'ok' : 'fail' ?>"><?= e((string) ($db_status['message'] ?? 'Não testado')) ?></span></p>
    </div>
</div>

<div class="card">
    <h2>Administrador inicial</h2>

    <?php if (!empty($errors)): ?>
        <div class="flash error">
            <?php foreach ($errors as $error): ?>
                <p style="margin:0 0 6px;"><?= e((string) $error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('install/index.php?route=run')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="grid two">
            <div>
                <label for="admin_name">Nome do administrador</label>
                <input id="admin_name" name="admin_name" value="<?= e((string) ($form['admin_name'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="admin_email">E-mail do administrador</label>
                <input id="admin_email" name="admin_email" type="email" value="<?= e((string) ($form['admin_email'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="admin_password">Senha</label>
                <input id="admin_password" name="admin_password" type="password" required>
            </div>
            <div>
                <label for="admin_password_confirm">Confirmar senha</label>
                <input id="admin_password_confirm" name="admin_password_confirm" type="password" required>
            </div>
        </div>

        <div class="actions">
            <button type="submit">Concluir instalação</button>
            <a class="button button-secondary" href="<?= e(base_url('install/index.php?route=step/2')) ?>">Voltar ao Passo 2</a>
        </div>
    </form>
</div>
