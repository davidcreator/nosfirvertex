<section class="card">
    <h1><i class="fa-solid fa-file-lines"></i> Crie currículos profissionais de forma rápida e gratuita</h1>
    <p class="muted">AureaVertex foi desenhado para funcionar muito bem em celular, com experiência simples, responsiva e orientada para quem busca recolocação profissional.</p>
    <div class="grid cols-2">
        <a class="button primary" href="<?= e(base_url('catalog/index.php?route=register')) ?>"><i class="fa-solid fa-user-plus"></i> Criar conta grátis</a>
        <a class="button" href="<?= e(base_url('catalog/index.php?route=templates')) ?>"><i class="fa-solid fa-layer-group"></i> Ver modelos</a>
    </div>
</section>

<?php if (!empty($ads_top)): ?>
    <section class="card">
        <h2>Parceiros</h2>
        <?php foreach ($ads_top as $ad): ?>
            <article class="card" style="margin-bottom:8px;">
                <?= strip_tags((string) $ad['content_html'], '<div><strong><em><a><p><span><br>') ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Por que usar o AureaVertex?</h2>
    <div class="grid cols-2">
        <article class="card">
            <h3><i class="fa-solid fa-wand-magic-sparkles"></i> Preenchimento assistido</h3>
            <p class="muted">Cada seção do currículo tem orientação prática com exemplos para melhorar qualidade e objetividade.</p>
        </article>
        <article class="card">
            <h3><i class="fa-solid fa-file-export"></i> Exportação PDF e digital</h3>
            <p class="muted">Baixe em PDF ou JSON estruturado para compartilhar em plataformas digitais e integrações futuras.</p>
        </article>
        <article class="card">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Salvar versões</h3>
            <p class="muted">Cada atualização gera histórico para você evoluir o currículo sem perder progresso.</p>
        </article>
        <article class="card">
            <h3><i class="fa-solid fa-mobile-screen-button"></i> Mobile first</h3>
            <p class="muted">Interface leve, legível e intuitiva para quem utiliza somente celular.</p>
        </article>
    </div>
</section>

<section class="card">
    <h2><i class="fa-solid fa-hand-holding-heart"></i> Gostou da plataforma?</h2>
    <p class="muted">O AureaVertex é gratuito. Se quiser ajudar com a continuidade do projeto, você pode contribuir de forma voluntária.</p>
    <a class="button" href="<?= e(base_url('catalog/index.php?route=doacoes')) ?>"><i class="fa-solid fa-gift"></i> Apoiar com doação</a>
</section>

<section class="card">
    <h2>Modelos demonstrativos</h2>
    <div class="grid cols-3">
        <?php foreach ($templates as $template): ?>
            <article class="card">
                <img src="<?= e(base_url((string) $template['image_path'])) ?>" alt="<?= e($template['name']) ?>" style="width:100%;border-radius:10px;border:1px solid var(--border);margin-bottom:8px;">
                <h3><?= e($template['name']) ?></h3>
                <p class="muted">Categoria: <?= e($template['category']) ?></p>
                <p class="muted"><?= e((string) ($template['description'] ?? 'Template demonstrativo para exportação profissional.')) ?></p>
                <a class="button primary" href="<?= e(base_url('catalog/index.php?route=resume/create&template_id=' . (int) ($template['template_id'] ?? 0))) ?>">Usar template</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($ads_mid)): ?>
    <section class="card">
        <?php foreach ($ads_mid as $ad): ?>
            <article class="card" style="margin-bottom:8px;">
                <?= strip_tags((string) $ad['content_html'], '<div><strong><em><a><p><span><br>') ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Fluxo recomendado</h2>
    <ol>
        <li>Crie sua conta e escolha um template.</li>
        <li>Preencha cada seção com apoio das dicas embutidas.</li>
        <li>Revise a prévia e faça ajustes.</li>
        <li>Exporte em PDF e em formato digital.</li>
    </ol>
</section>

<?php if (!empty($ads_footer)): ?>
    <section class="card">
        <?php foreach ($ads_footer as $ad): ?>
            <article class="card" style="margin-bottom:8px;">
                <?= strip_tags((string) $ad['content_html'], '<div><strong><em><a><p><span><br>') ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
