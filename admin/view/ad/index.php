<section class="card">
    <h1>Gerenciamento de anúncios</h1>
    <p class="muted">Configure blocos discretos para sustentar a plataforma sem prejudicar UX.</p>

    <form method="post" action="<?= e(base_url('admin/index.php?route=ads')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="ad_block_id" value="0">

        <div class="grid cols-3">
            <div>
                <label for="name">Nome do bloco</label>
                <input id="name" name="name" required>
            </div>
            <div>
                <label for="position_code">Posição</label>
                <input id="position_code" name="position_code" placeholder="home_top, home_mid..." required>
            </div>
            <div>
                <label for="display_order">Ordem</label>
                <input id="display_order" name="display_order" type="number" value="0" required>
            </div>
        </div>

        <label for="content_html">HTML do bloco</label>
        <textarea id="content_html" name="content_html" required></textarea>

        <label><input type="checkbox" name="is_active" value="1" checked> Ativo</label>
        <button type="submit">Salvar anúncio</button>
    </form>
</section>

<section class="card">
    <h2>Blocos cadastrados</h2>
    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Posição</th>
                <th>Ordem</th>
                <th>Ativo</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td><?= (int) $ad['ad_block_id'] ?></td>
                    <td><?= e($ad['name']) ?></td>
                    <td><?= e($ad['position_code']) ?></td>
                    <td><?= (int) $ad['display_order'] ?></td>
                    <td><?= (int) $ad['is_active'] === 1 ? 'Sim' : 'Não' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
