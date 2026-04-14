<section class="card" style="max-width:520px;margin:0 auto;">
    <h1>Login administrativo</h1>
    <p class="muted">Acesso restrito para gestão da plataforma.</p>

    <?php if (!empty($error)): ?>
        <div class="flash error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('admin/index.php?route=login')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" required>

        <label for="password">Senha</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Entrar no admin</button>
    </form>
</section>
