<section class="card">
    <h1>Gerenciamento de currículos</h1>
    <p class="muted">Acompanhamento dos currículos criados na área pública.</p>

    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Usuário</th>
                <th>E-mail</th>
                <th>Status</th>
                <th>Template</th>
                <th>Atualizado em</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resumes as $resume): ?>
                <tr>
                    <td><?= (int) $resume['resume_id'] ?></td>
                    <td><?= e($resume['title']) ?></td>
                    <td><?= e($resume['full_name']) ?></td>
                    <td><?= e($resume['email']) ?></td>
                    <td><?= e($resume['status']) ?></td>
                    <td><?= e((string) ($resume['template_name'] ?? '-')) ?></td>
                    <td><?= e((string) ($resume['updated_at'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
