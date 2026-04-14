<section class="card">
    <h1>Gerenciamento de templates</h1>
    <p class="muted">Crie e ajuste os modelos demonstrativos exibidos no catálogo.</p>

    <form method="post" action="<?= e(base_url('admin/index.php?route=templates')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="template_id" value="0">

        <div class="grid cols-3">
            <div>
                <label for="name">Nome</label>
                <input id="name" name="name" required>
            </div>
            <div>
                <label for="category">Categoria</label>
                <input id="category" name="category" placeholder="basico, moderno..." required>
            </div>
            <div>
                <label for="image_path">Imagem</label>
                <input id="image_path" name="image_path" placeholder="image/templates/modelo.svg" required>
            </div>
        </div>

        <label for="description">Descrição</label>
        <textarea id="description" name="description"></textarea>

        <label><input type="checkbox" name="is_active" value="1" checked> Ativo</label>
        <button type="submit">Salvar template</button>
    </form>
</section>

<section class="card">
    <h2>Templates cadastrados</h2>
    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Imagem</th>
                <th>Ativo</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($templates as $template): ?>
                <tr>
                    <td><?= (int) $template['template_id'] ?></td>
                    <td><?= e($template['name']) ?></td>
                    <td><?= e($template['category']) ?></td>
                    <td><?= e($template['image_path']) ?></td>
                    <td><?= (int) $template['is_active'] === 1 ? 'Sim' : 'Não' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
