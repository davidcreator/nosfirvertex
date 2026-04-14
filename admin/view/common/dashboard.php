<section class="card">
    <h1>Dashboard administrativo</h1>
    <p class="muted">Visão geral do sistema NosfirVertex.</p>
</section>

<section class="grid cols-3">
    <article class="card">
        <h2>Usuários</h2>
        <p style="font-size:30px;font-weight:700;"><?= (int) ($total_users ?? 0) ?></p>
    </article>
    <article class="card">
        <h2>Currículos</h2>
        <p style="font-size:30px;font-weight:700;"><?= (int) ($total_resumes ?? 0) ?></p>
    </article>
    <article class="card">
        <h2>Templates</h2>
        <p style="font-size:30px;font-weight:700;"><?= (int) ($total_templates ?? 0) ?></p>
    </article>
</section>
