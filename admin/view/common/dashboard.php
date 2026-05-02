<section class="card dashboard-hero">
    <div class="hero-content">
        <h1><i class="fa-solid fa-chart-pie"></i> <?= e(lang('Dashboard administrativo')) ?></h1>
        <p class="muted"><?= e(lang('Visão geral do sistema Vertex.')) ?></p>
    </div>
    <div class="section-actions">
        <a class="button primary" href="<?= e(base_url('admin/index.php?route=users')) ?>"><i class="fa-solid fa-users"></i> <?= e(lang('Usuários')) ?></a>
        <a class="button secondary" href="<?= e(base_url('admin/index.php?route=resumes')) ?>"><i class="fa-solid fa-file-lines"></i> <?= e(lang('Currículos')) ?></a>
    </div>
</section>

<section class="grid cols-3">
    <article class="card">
        <h2><i class="fa-solid fa-users"></i> <?= e(lang('Usuários')) ?></h2>
        <p class="metric-value"><?= (int) ($total_users ?? 0) ?></p>
        <p class="metric-caption"><?= e(lang('Usuários cadastrados')) ?></p>
    </article>

    <article class="card">
        <h2><i class="fa-solid fa-file-lines"></i> <?= e(lang('Currículos')) ?></h2>
        <p class="metric-value"><?= (int) ($total_resumes ?? 0) ?></p>
        <p class="metric-caption"><?= e(lang('Currículos registrados na plataforma')) ?></p>
    </article>

    <article class="card">
        <h2><i class="fa-solid fa-layer-group"></i> <?= e(lang('Templates')) ?></h2>
        <p class="metric-value"><?= (int) ($total_templates ?? 0) ?></p>
        <p class="metric-caption"><?= e(lang('Modelos disponíveis para criação')) ?></p>
    </article>

    <article class="card">
        <h2><i class="fa-solid fa-rectangle-ad"></i> <?= e(lang('Anúncios')) ?></h2>
        <p class="metric-value"><?= (int) ($total_ads ?? 0) ?></p>
        <p class="metric-caption"><?= e(lang('Blocos de anúncio configurados')) ?></p>
    </article>

    <article class="card">
        <h2><i class="fa-solid fa-list-check"></i> <?= e(lang('Logs')) ?></h2>
        <p class="metric-value"><?= (int) ($total_logs ?? 0) ?></p>
        <p class="metric-caption"><?= e(lang('Eventos registrados no sistema')) ?></p>
    </article>

    <article class="card">
        <h2><i class="fa-solid fa-gears"></i> <?= e(lang('Configurações')) ?></h2>
        <p class="muted"><?= e(lang('Ajuste os parâmetros globais do Vertex')) ?></p>
        <a class="button" href="<?= e(base_url('admin/index.php?route=settings')) ?>"><i class="fa-solid fa-sliders"></i> <?= e(lang('Abrir configurações')) ?></a>
    </article>
</section>

<section class="card">
    <h2><i class="fa-solid fa-bolt"></i> <?= e(lang('Atalhos rápidos')) ?></h2>
    <p class="muted"><?= e(lang('Acesse rapidamente os principais módulos administrativos.')) ?></p>
    <div class="section-actions">
        <a class="button" href="<?= e(base_url('admin/index.php?route=users')) ?>"><i class="fa-solid fa-users"></i> <?= e(lang('Usuários')) ?></a>
        <a class="button" href="<?= e(base_url('admin/index.php?route=resumes')) ?>"><i class="fa-solid fa-file-lines"></i> <?= e(lang('Currículos')) ?></a>
        <a class="button" href="<?= e(base_url('admin/index.php?route=templates')) ?>"><i class="fa-solid fa-layer-group"></i> <?= e(lang('Templates')) ?></a>
        <a class="button" href="<?= e(base_url('admin/index.php?route=ads')) ?>"><i class="fa-solid fa-rectangle-ad"></i> <?= e(lang('Anúncios')) ?></a>
        <a class="button" href="<?= e(base_url('admin/index.php?route=logs')) ?>"><i class="fa-solid fa-list-check"></i> <?= e(lang('Logs')) ?></a>
    </div>
</section>
