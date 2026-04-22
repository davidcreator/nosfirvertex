<section class="card">
    <h1>Painel do usuário</h1>
    <p class="muted">Gerencie seus currículos, versões e exportações.</p>
    <a class="button primary" href="<?= e(base_url('catalog/index.php?route=resume/create')) ?>">Criar novo currículo</a>
</section>

<section class="card">
    <h2>Meus currículos</h2>

    <?php if (empty($resumes)): ?>
        <p class="muted">Você ainda não criou nenhum currículo.</p>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Status</th>
                    <th>Template</th>
                    <th>Atualizado em</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($resumes as $resume): ?>
                    <tr>
                        <td><?= e($resume['title']) ?></td>
                        <td><?= e($resume['status']) ?></td>
                        <td><?= e((string) ($resume['template_name'] ?? '-')) ?></td>
                        <td><?= e((string) ($resume['updated_at'] ?? '-')) ?></td>
                        <td>
                            <a href="<?= e(base_url('catalog/index.php?route=resume/view/' . $resume['resume_id'])) ?>">Ver</a> |
                            <a href="<?= e(base_url('catalog/index.php?route=resume/edit/' . $resume['resume_id'])) ?>">Editar</a> |
                            <a href="<?= e(base_url('catalog/index.php?route=resume/export/pdf/' . $resume['resume_id'])) ?>">PDF</a> |
                            <a href="<?= e(base_url('catalog/index.php?route=resume/export/docx/' . $resume['resume_id'])) ?>">DOCX</a> |
                            <a href="<?= e(base_url('catalog/index.php?route=resume/export/json/' . $resume['resume_id'])) ?>">JSON</a>
                            <form method="post" action="<?= e(base_url('catalog/index.php?route=resume/delete/' . $resume['resume_id'])) ?>" style="display:inline;" onsubmit="return confirm('Remover currículo?');">
                                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" style="border:0;background:transparent;color:#b83434;cursor:pointer;padding:0;margin-left:6px;">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
