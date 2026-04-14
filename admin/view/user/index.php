<section class="card">
    <h1>Gerenciamento de usuários</h1>
    <p class="muted">Controle de contas e status de acesso.</p>

    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Papel</th>
                <th>Status</th>
                <th>Criado em</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= (int) $user['user_id'] ?></td>
                    <td><?= e($user['full_name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['role']) ?></td>
                    <td><?= e($user['status']) ?></td>
                    <td><?= e((string) ($user['created_at'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
