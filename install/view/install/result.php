<div class="card">
    <h1><?= $success ? 'Instalação concluída' : 'Instalação com pendências' ?></h1>
    <?php foreach ($messages as $message): ?>
        <p class="<?= $success ? 'ok' : 'fail' ?>"><?= e($message) ?></p>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <p><a href="<?= e($catalog_url ?? base_url('catalog/index.php')) ?>">Ir para o catálogo</a></p>
        <p><a href="<?= e($admin_url ?? base_url('admin/index.php')) ?>">Ir para o admin</a></p>
    <?php else: ?>
        <p><a href="<?= e(base_url('install/index.php?route=step/1')) ?>">Voltar ao passo 1</a></p>
    <?php endif; ?>
</div>
