<section class="card" style="max-width:620px;margin:0 auto;">
    <h1>Criar conta gratuita</h1>
    <p class="muted">Em poucos passos você já começa a montar seu currículo.</p>

    <?php if (!empty($error)): ?>
        <div class="flash error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(base_url('catalog/index.php?route=register')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <label for="full_name">Nome completo</label>
        <input id="full_name" name="full_name" required>

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" required>

        <label for="password">Senha (mínimo 8 caracteres)</label>
        <input id="password" name="password" type="password" required>

        <button class="button primary" type="submit">Cadastrar</button>
    </form>
</section>
