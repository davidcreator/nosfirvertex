<?php
$filters = is_array($filters ?? null) ? $filters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];

$currentPage = max(1, (int) ($pagination['page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$perPage = (int) ($pagination['per_page'] ?? 20);
$total = (int) ($pagination['total'] ?? 0);

$baseParams = [
    'q' => (string) ($filters['q'] ?? ''),
    'status' => (string) ($filters['status'] ?? ''),
    'updated_from' => (string) ($filters['updated_from'] ?? ''),
    'updated_to' => (string) ($filters['updated_to'] ?? ''),
    'per_page' => (string) $perPage,
];

$buildUrl = static function (array $overrides = []) use ($baseParams): string {
    $params = array_merge($baseParams, $overrides);
    $params = array_filter(
        $params,
        static fn ($value): bool => $value !== '' && $value !== null
    );
    $params = array_merge(['route' => 'resumes'], $params);

    return base_url('admin/index.php?' . http_build_query($params));
};

$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);
?>

<section class="card">
    <h1>Gerenciamento de curriculos</h1>
    <p class="muted">Acompanhamento dos curriculos criados na area publica.</p>

    <form method="get" action="<?= e(base_url('admin/index.php')) ?>">
        <input type="hidden" name="route" value="resumes">
        <div class="grid cols-3">
            <div>
                <label for="q">Busca</label>
                <input id="q" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Titulo, usuario ou e-mail">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="draft"<?= (($filters['status'] ?? '') === 'draft') ? ' selected' : '' ?>>Rascunho</option>
                    <option value="published"<?= (($filters['status'] ?? '') === 'published') ? ' selected' : '' ?>>Publicado</option>
                </select>
            </div>
            <div>
                <label for="per_page">Itens por pagina</label>
                <select id="per_page" name="per_page">
                    <?php foreach ([20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>"<?= $perPage === $size ? ' selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="updated_from">Atualizado de</label>
                <input id="updated_from" type="date" name="updated_from" value="<?= e((string) ($filters['updated_from'] ?? '')) ?>">
            </div>
            <div>
                <label for="updated_to">Atualizado ate</label>
                <input id="updated_to" type="date" name="updated_to" value="<?= e((string) ($filters['updated_to'] ?? '')) ?>">
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit">Filtrar</button>
            <a class="button" href="<?= e(base_url('admin/index.php?route=resumes')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <p class="muted">Total encontrado: <?= $total ?></p>

    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Titulo</th>
                <th>Usuario</th>
                <th>E-mail</th>
                <th>Status</th>
                <th>Template</th>
                <th>Atualizado em</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($resumes)): ?>
                <?php foreach ($resumes as $resume): ?>
                    <tr>
                        <td><?= (int) $resume['resume_id'] ?></td>
                        <td><?= e((string) ($resume['title'] ?? '')) ?></td>
                        <td><?= e((string) ($resume['full_name'] ?? '')) ?></td>
                        <td><?= e((string) ($resume['email'] ?? '')) ?></td>
                        <td><?= e((string) ($resume['status'] ?? '')) ?></td>
                        <td><?= e((string) ($resume['template_name'] ?? '-')) ?></td>
                        <td><?= e((string) ($resume['updated_at'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="muted">Nenhum curriculo encontrado para os filtros aplicados.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <?php if ($currentPage > 1): ?>
                <a class="button" href="<?= e($buildUrl(['page' => $currentPage - 1])) ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                <?php if ($page === $currentPage): ?>
                    <span class="button primary" style="pointer-events:none;"><?= $page ?></span>
                <?php else: ?>
                    <a class="button" href="<?= e($buildUrl(['page' => $page])) ?>"><?= $page ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a class="button" href="<?= e($buildUrl(['page' => $currentPage + 1])) ?>">Proxima</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
