<section class="card" style="max-width:620px;margin:0 auto;">
    <h1>Redefinir senha</h1>
    <p class="muted">Informe a nova senha para concluir a recuperação.</p>

    <?php if (!empty($error)): ?>
        <div class="flash error"><?= e((string) $error) ?></div>
    <?php endif; ?>

    <?php if (!isset($can_submit) || $can_submit === true): ?>
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

            <label for="password">Nova senha</label>
            <input id="password" name="password" type="password" minlength="8" required>

            <label for="password_confirm">Confirmar nova senha</label>
            <input id="password_confirm" name="password_confirm" type="password" minlength="8" required>

            <button class="button primary" type="submit">Salvar nova senha</button>
        </form>
    <?php endif; ?>
</section>
