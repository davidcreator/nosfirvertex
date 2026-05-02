<section class="card">
    <h1><?= e(lang('Gerenciamento de templates')) ?></h1>
    <p class="muted"><?= e(lang('Crie e ajuste os modelos demonstrativos exibidos no catálogo.')) ?></p>

    <form method="post" action="<?= e(base_url('admin/index.php?route=templates')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="template_id" value="0">

        <div class="grid cols-3">
            <div>
                <label for="name"><?= e(lang('Nome')) ?></label>
                <input id="name" name="name" required>
            </div>
            <div>
                <label for="category"><?= e(lang('Categoria')) ?></label>
                <input id="category" name="category" placeholder="basico, moderno..." required>
            </div>
            <div>
                <label for="image_path"><?= e(lang('Imagem')) ?></label>
                <input id="image_path" name="image_path" placeholder="image/templates/modelo.svg" required>
            </div>
        </div>

        <label for="description"><?= e(lang('Descrição')) ?></label>
        <textarea id="description" name="description"></textarea>

        <label class="inline-check">
            <input type="checkbox" name="is_active" value="1" checked>
            <span><?= e(lang('Ativo')) ?></span>
        </label>

        <button type="submit"><?= e(lang('Salvar template')) ?></button>
    </form>
</section>

<section class="card">
    <h2><?= e(lang('Templates cadastrados')) ?></h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th><?= e(lang('Nome')) ?></th>
                <th><?= e(lang('Categoria')) ?></th>
                <th><?= e(lang('Imagem')) ?></th>
                <th><?= e(lang('Ativo')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $template): ?>
                <tr>
                    <td><?= (int) $template['template_id'] ?></td>
                    <td><?= e($template['name']) ?></td>
                    <td><?= e($template['category']) ?></td>
                    <td><?= e($template['image_path']) ?></td>
                    <td><?= (int) $template['is_active'] === 1 ? e(lang('Sim')) : e(lang('Não')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
