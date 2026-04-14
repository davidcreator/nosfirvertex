<section class="card">
    <h1>Configurações da conta</h1>
    <p class="muted">Atualize seus dados para facilitar o preenchimento dos currículos.</p>

    <form method="post" action="<?= e(base_url('catalog/index.php?route=account/settings')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <div class="grid cols-2">
            <div>
                <label for="full_name">Nome completo</label>
                <input id="full_name" name="full_name" value="<?= e((string) ($user['full_name'] ?? '')) ?>" required>
            </div>
            <div>
                <label for="phone">Telefone</label>
                <input id="phone" name="phone" value="<?= e((string) ($user['phone'] ?? '')) ?>">
            </div>
            <div>
                <label for="city">Cidade</label>
                <input id="city" name="city" value="<?= e((string) ($user['city'] ?? '')) ?>">
            </div>
            <div>
                <label for="state">Estado</label>
                <input id="state" name="state" value="<?= e((string) ($user['state'] ?? '')) ?>">
            </div>
            <div>
                <label for="country">País</label>
                <input id="country" name="country" value="<?= e((string) ($user['country'] ?? '')) ?>">
            </div>
            <div>
                <label for="website">Website</label>
                <input id="website" name="website" value="<?= e((string) ($user['website'] ?? '')) ?>">
            </div>
            <div>
                <label for="linkedin">LinkedIn</label>
                <input id="linkedin" name="linkedin" value="<?= e((string) ($user['linkedin'] ?? '')) ?>">
            </div>
            <div>
                <label for="github">GitHub/Portfólio</label>
                <input id="github" name="github" value="<?= e((string) ($user['github'] ?? '')) ?>">
            </div>
            <div>
                <label for="new_password">Nova senha (opcional)</label>
                <input id="new_password" name="new_password" type="password">
            </div>
        </div>

        <button class="button primary" type="submit">Salvar alterações</button>
    </form>
</section>
