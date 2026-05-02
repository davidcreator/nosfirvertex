<section class="card auth-card">
    <div class="auth-head">
        <span class="auth-badge">
            <img src="<?= e(base_url('image/vertex_logo_wt.png')) ?>" alt="Vertex">
            <?= e(lang('Vertex Admin')) ?>
        </span>
        <h1><i class="fa-solid fa-lock"></i> <?= e(lang('Acesso administrativo protegido')) ?></h1>
        <p class="muted"><?= e(lang('Área administrativa com monitoramento e controle de módulos.')) ?></p>

        <ul class="auth-feature-list">
            <li><i class="fa-solid fa-users-gear"></i> <?= e(lang('Gestão de usuários e permissões por perfil')) ?></li>
            <li><i class="fa-solid fa-file-lines"></i> <?= e(lang('Acompanhamento de currículos e templates')) ?></li>
            <li><i class="fa-solid fa-shield-halved"></i> <?= e(lang('Fluxo protegido para governança do sistema')) ?></li>
        </ul>
    </div>

    <form method="post" action="<?= e(base_url('admin/index.php?route=login')) ?>" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($error)): ?>
            <div class="flash error"><?= e($error) ?></div>
        <?php endif; ?>

        <label for="email"><?= e(lang('E-mail')) ?></label>
        <input id="email" name="email" type="email" autocomplete="username" required>

        <label for="password"><?= e(lang('Senha')) ?></label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button type="submit"><i class="fa-solid fa-right-to-bracket"></i> <?= e(lang('Entrar no admin')) ?></button>
    </form>
</section>
