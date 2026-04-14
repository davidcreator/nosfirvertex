<section class="card" style="max-width:620px;margin:0 auto;">
    <h1>Recuperação de senha</h1>
    <p class="muted">Informe seu e-mail para iniciar o processo de recuperação.</p>

    <?php if (!empty($message)): ?>
        <div class="flash success"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('catalog/index.php?route=password/forgot')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" required>

        <button class="button primary" type="submit">Solicitar recuperação</button>
    </form>
</section>
