<?php
$filters = is_array($filters ?? null) ? $filters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];

$currentPage = max(1, (int) ($pagination['page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$perPage = (int) ($pagination['per_page'] ?? 20);
$total = (int) ($pagination['total'] ?? 0);

$baseParams = [
    'q' => (string) ($filters['q'] ?? ''),
    'role' => (string) ($filters['role'] ?? ''),
    'status' => (string) ($filters['status'] ?? ''),
    'created_from' => (string) ($filters['created_from'] ?? ''),
    'created_to' => (string) ($filters['created_to'] ?? ''),
    'per_page' => (string) $perPage,
];

$buildUrl = static function (array $overrides = []) use ($baseParams): string {
    $params = array_merge($baseParams, $overrides);
    $params = array_filter(
        $params,
        static fn ($value): bool => $value !== '' && $value !== null
    );
    $params = array_merge(['route' => 'users'], $params);

    return base_url('admin/index.php?' . http_build_query($params));
};

$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);
?>

<section class="card">
    <h1><?= e(lang('Gerenciamento de usuarios')) ?></h1>
    <p class="muted"><?= e(lang('Controle de contas e status de acesso.')) ?></p>

    <form method="get" action="<?= e(base_url('admin/index.php')) ?>">
        <input type="hidden" name="route" value="users">
        <div class="grid cols-3">
            <div>
                <label for="q"><?= e(lang('Busca')) ?></label>
                <input id="q" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="<?= e(lang('Nome ou e-mail')) ?>">
            </div>
            <div>
                <label for="role"><?= e(lang('Papel')) ?></label>
                <select id="role" name="role">
                    <option value=""><?= e(lang('Todos')) ?></option>
                    <option value="user"<?= (($filters['role'] ?? '') === 'user') ? ' selected' : '' ?>><?= e(lang('Usuario')) ?></option>
                    <option value="admin"<?= (($filters['role'] ?? '') === 'admin') ? ' selected' : '' ?>><?= e(lang('Admin')) ?></option>
                </select>
            </div>
            <div>
                <label for="status"><?= e(lang('Status')) ?></label>
                <select id="status" name="status">
                    <option value=""><?= e(lang('Todos')) ?></option>
                    <option value="active"<?= (($filters['status'] ?? '') === 'active') ? ' selected' : '' ?>><?= e(lang('Ativo')) ?></option>
                    <option value="inactive"<?= (($filters['status'] ?? '') === 'inactive') ? ' selected' : '' ?>><?= e(lang('Inativo')) ?></option>
                </select>
            </div>
            <div>
                <label for="created_from"><?= e(lang('Criado de')) ?></label>
                <input id="created_from" type="date" name="created_from" value="<?= e((string) ($filters['created_from'] ?? '')) ?>">
            </div>
            <div>
                <label for="created_to"><?= e(lang('Criado ate')) ?></label>
                <input id="created_to" type="date" name="created_to" value="<?= e((string) ($filters['created_to'] ?? '')) ?>">
            </div>
            <div>
                <label for="per_page"><?= e(lang('Itens por pagina')) ?></label>
                <select id="per_page" name="per_page">
                    <?php foreach ([20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>"<?= $perPage === $size ? ' selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="section-actions">
            <button type="submit"><?= e(lang('Filtrar')) ?></button>
            <a class="button secondary" href="<?= e(base_url('admin/index.php?route=users')) ?>"><?= e(lang('Limpar')) ?></a>
        </div>
    </form>
</section>

<section class="card">
    <p class="muted"><?= e(lang('Total encontrado:')) ?> <?= $total ?></p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th><?= e(lang('Nome')) ?></th>
                <th><?= e(lang('E-mail')) ?></th>
                <th><?= e(lang('Papel')) ?></th>
                <th><?= e(lang('Status')) ?></th>
                <th><?= e(lang('Criado em')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['user_id'] ?></td>
                        <td><?= e((string) ($user['full_name'] ?? '')) ?></td>
                        <td><?= e((string) ($user['email'] ?? '')) ?></td>
                        <td><?= e((string) ($user['role'] ?? '')) ?></td>
                        <td><?= e((string) ($user['status'] ?? '')) ?></td>
                        <td><?= e((string) ($user['created_at'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="muted"><?= e(lang('Nenhum usuario encontrado para os filtros aplicados.')) ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pager">
            <?php if ($currentPage > 1): ?>
                <a class="button secondary" href="<?= e($buildUrl(['page' => $currentPage - 1])) ?>"><?= e(lang('Anterior')) ?></a>
            <?php endif; ?>

            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                <?php if ($page === $currentPage): ?>
                    <span class="button is-current"><?= $page ?></span>
                <?php else: ?>
                    <a class="button secondary" href="<?= e($buildUrl(['page' => $page])) ?>"><?= $page ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a class="button secondary" href="<?= e($buildUrl(['page' => $currentPage + 1])) ?>"><?= e(lang('Proxima')) ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
