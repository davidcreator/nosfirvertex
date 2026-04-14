<section class="card">
    <h1>Modelos de currículo</h1>
    <p class="muted">Escolha um estilo alinhado com sua área. Você poderá alternar o template durante as edições.</p>
</section>

<section class="grid cols-3">
    <?php foreach ($templates as $template): ?>
        <article class="card">
            <img src="<?= e(base_url((string) $template['image_path'])) ?>" alt="<?= e($template['name']) ?>" style="width:100%;border-radius:10px;border:1px solid var(--border);margin-bottom:8px;">
            <h2><?= e($template['name']) ?></h2>
            <p class="muted">Categoria: <?= e($template['category']) ?></p>
            <p class="muted"><?= e((string) ($template['description'] ?? 'Template demonstrativo.')) ?></p>
            <a class="button primary" href="<?= e(base_url('catalog/index.php?route=resume/create&template_id=' . (int) ($template['template_id'] ?? 0))) ?>">Usar template</a>
        </article>
    <?php endforeach; ?>
</section>
