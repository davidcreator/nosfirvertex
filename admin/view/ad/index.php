<section class="card">
    <h1><?= e(lang('Gerenciamento de anúncios')) ?></h1>
    <p class="muted"><?= e(lang('Configure blocos discretos para sustentar a plataforma sem prejudicar UX.')) ?></p>

    <form method="post" action="<?= e(base_url('admin/index.php?route=ads')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="ad_block_id" value="0">

        <div class="grid cols-3">
            <div>
                <label for="name"><?= e(lang('Nome do bloco')) ?></label>
                <input id="name" name="name" required>
            </div>
            <div>
                <label for="position_code"><?= e(lang('Posição')) ?></label>
                <input id="position_code" name="position_code" placeholder="home_top, home_mid..." required>
            </div>
            <div>
                <label for="display_order"><?= e(lang('Ordem')) ?></label>
                <input id="display_order" name="display_order" type="number" value="0" required>
            </div>
        </div>

        <label for="content_html"><?= e(lang('HTML do bloco')) ?></label>
        <textarea id="content_html" name="content_html" required></textarea>

        <label class="inline-check">
            <input type="checkbox" name="is_active" value="1" checked>
            <span><?= e(lang('Ativo')) ?></span>
        </label>

        <button type="submit"><?= e(lang('Salvar anúncio')) ?></button>
    </form>
</section>

<section class="card">
    <h2><?= e(lang('Blocos cadastrados')) ?></h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th><?= e(lang('Nome')) ?></th>
                <th><?= e(lang('Posição')) ?></th>
                <th><?= e(lang('Ordem')) ?></th>
                <th><?= e(lang('Ativo')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ads as $ad): ?>
                <tr>
                    <td><?= (int) $ad['ad_block_id'] ?></td>
                    <td><?= e($ad['name']) ?></td>
                    <td><?= e($ad['position_code']) ?></td>
                    <td><?= (int) $ad['display_order'] ?></td>
                    <td><?= (int) $ad['is_active'] === 1 ? e(lang('Sim')) : e(lang('Não')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
