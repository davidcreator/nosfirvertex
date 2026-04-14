<div class="card">
    <h1>Instalador AureaVertex - Passo 1</h1>
    <p>Valide os requisitos do servidor e as permissões de escrita antes de configurar o banco.</p>
</div>

<div class="card">
    <h2>Requisitos do servidor</h2>
    <table>
        <thead>
        <tr>
            <th>Item</th>
            <th>Atual</th>
            <th>Requerido</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requirements as $item): ?>
            <tr>
                <td><?= e($item['name']) ?></td>
                <td><?= e((string) $item['current']) ?></td>
                <td><?= e((string) $item['required']) ?></td>
                <td class="<?= $item['status'] ? 'ok' : 'fail' ?>"><?= $item['status'] ? 'OK' : 'Falha' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Permissões de escrita</h2>
    <table>
        <thead>
        <tr>
            <th>Caminho</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($permissions as $item): ?>
            <tr>
                <td><?= e($item['path']) ?></td>
                <td class="<?= $item['status'] ? 'ok' : 'fail' ?>"><?= $item['status'] ? 'Gravável' : 'Sem permissão' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p class="<?= $can_continue ? 'ok' : 'fail' ?>" style="margin-top:12px;">
        <?= $can_continue ? 'Ambiente validado. Você pode seguir para o Passo 2.' : 'Ainda há pendências no ambiente. Corrija para continuar.' ?>
    </p>

    <form method="post" action="<?= e(base_url('install/index.php?route=step/1/next')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <div class="actions">
            <button type="submit" <?= $can_continue ? '' : 'disabled' ?>>Ir para o Passo 2</button>
            <a class="button button-secondary" href="<?= e(base_url('install/index.php?route=restart')) ?>">Reiniciar assistente</a>
        </div>
    </form>
</div>
