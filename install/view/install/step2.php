<div class="card">
    <h1>Instalador NosfirVertex - Passo 2</h1>
    <p>Configure a conexão com o banco e teste a comunicação antes de prosseguir.</p>
</div>

<div class="card">
    <h2>Conexão com banco</h2>

    <form method="post" action="<?= e(base_url('install/index.php?route=step/2/test-db')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="grid">
            <label for="base_url">URL base do sistema</label>
            <input id="base_url" name="base_url" value="<?= e((string) ($form['base_url'] ?? '')) ?>" required>
        </div>

        <div class="grid two">
            <div>
                <label for="db_host">Host DB</label>
                <input id="db_host" name="db_host" value="<?= e((string) ($form['db_host'] ?? '127.0.0.1')) ?>" required>
            </div>
            <div>
                <label for="db_port">Porta DB</label>
                <input id="db_port" name="db_port" value="<?= e((string) ($form['db_port'] ?? '3306')) ?>" required>
            </div>
            <div>
                <label for="db_name">Nome do banco</label>
                <input id="db_name" name="db_name" value="<?= e((string) ($form['db_name'] ?? 'nosfirvertex')) ?>" required>
            </div>
            <div>
                <label for="db_user">Usuário DB</label>
                <input id="db_user" name="db_user" value="<?= e((string) ($form['db_user'] ?? 'root')) ?>" required>
            </div>
            <div>
                <label for="db_password">Senha DB</label>
                <input id="db_password" name="db_password" type="password" value="<?= e((string) ($form['db_password'] ?? '')) ?>">
            </div>
        </div>

        <div class="checkbox-row">
            <input id="db_create_if_missing" type="checkbox" name="db_create_if_missing" value="1" <?= ((string) ($form['db_create_if_missing'] ?? '1')) === '1' ? 'checked' : '' ?>>
            <label for="db_create_if_missing" style="margin:0;">Criar banco automaticamente se não existir</label>
        </div>

        <div class="actions">
            <button type="submit">Testar conexão</button>
            <a class="button button-secondary" href="<?= e(base_url('install/index.php?route=step/1')) ?>">Voltar ao Passo 1</a>
        </div>
    </form>
</div>

<div class="card">
    <h2>Status da comunicação com o banco</h2>
    <p class="<?= !empty($db_status['success']) ? 'ok' : 'warn' ?>"><?= e((string) ($db_status['message'] ?? 'Sem diagnóstico ainda.')) ?></p>

    <?php if (!empty($db_status['details']) && is_array($db_status['details'])): ?>
        <div class="details">
            <p><strong>Host:</strong> <?= e((string) ($db_status['details']['host'] ?? '-')) ?>:<?= e((string) ($db_status['details']['port'] ?? '-')) ?></p>
            <p><strong>Banco alvo:</strong> <?= e((string) ($db_status['details']['database'] ?? '-')) ?></p>
            <p><strong>Banco existe:</strong> <?= !empty($db_status['details']['database_exists']) ? 'Sim' : 'Não' ?></p>
            <p><strong>Versão do servidor:</strong> <?= e((string) ($db_status['details']['server_version'] ?? '-')) ?></p>
            <p><strong>Autenticado como:</strong> <?= e((string) ($db_status['details']['authenticated_as'] ?? '-')) ?></p>

            <?php if (!empty($db_status['details']['database_preview']) && is_array($db_status['details']['database_preview'])): ?>
                <p><strong>Amostra de bancos disponíveis:</strong></p>
                <ul>
                    <?php foreach ($db_status['details']['database_preview'] as $schema): ?>
                        <li><?= e((string) $schema) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('install/index.php?route=step/2/next')) ?>" style="margin-top:12px;">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="base_url" value="<?= e((string) ($form['base_url'] ?? '')) ?>">
        <input type="hidden" name="db_host" value="<?= e((string) ($form['db_host'] ?? '')) ?>">
        <input type="hidden" name="db_port" value="<?= e((string) ($form['db_port'] ?? '')) ?>">
        <input type="hidden" name="db_name" value="<?= e((string) ($form['db_name'] ?? '')) ?>">
        <input type="hidden" name="db_user" value="<?= e((string) ($form['db_user'] ?? '')) ?>">
        <input type="hidden" name="db_password" value="<?= e((string) ($form['db_password'] ?? '')) ?>">
        <input type="hidden" name="db_create_if_missing" value="<?= ((string) ($form['db_create_if_missing'] ?? '1')) === '1' ? '1' : '0' ?>">

        <button type="submit" <?= !empty($can_continue) ? '' : 'disabled' ?>>Ir para o Passo 3</button>
    </form>
</div>
