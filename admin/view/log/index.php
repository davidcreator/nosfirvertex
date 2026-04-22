<?php
$filters = is_array($filters ?? null) ? $filters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];

$currentPage = max(1, (int) ($pagination['page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$perPage = (int) ($pagination['per_page'] ?? 20);
$total = (int) ($pagination['total'] ?? 0);

$baseParams = [
    'q' => (string) ($filters['q'] ?? ''),
    'level' => (string) ($filters['level'] ?? ''),
    'context' => (string) ($filters['context'] ?? ''),
    'request_id' => (string) ($filters['request_id'] ?? ''),
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
    $params = array_merge(['route' => 'logs'], $params);

    return base_url('admin/index.php?' . http_build_query($params));
};

$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);
?>

<section class="card">
    <h1>Logs do sistema</h1>
    <p class="muted">Monitoramento de eventos e falhas basicas.</p>

    <form method="get" action="<?= e(base_url('admin/index.php')) ?>">
        <input type="hidden" name="route" value="logs">
        <div class="grid cols-3">
            <div>
                <label for="q">Busca</label>
                <input id="q" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Mensagem ou contexto">
            </div>
            <div>
                <label for="level">Nivel</label>
                <select id="level" name="level">
                    <option value="">Todos</option>
                    <option value="info"<?= (($filters['level'] ?? '') === 'info') ? ' selected' : '' ?>>Info</option>
                    <option value="warning"<?= (($filters['level'] ?? '') === 'warning') ? ' selected' : '' ?>>Warning</option>
                    <option value="error"<?= (($filters['level'] ?? '') === 'error') ? ' selected' : '' ?>>Error</option>
                </select>
            </div>
            <div>
                <label for="context">Contexto</label>
                <input id="context" name="context" value="<?= e((string) ($filters['context'] ?? '')) ?>" placeholder="catalog, admin, system...">
            </div>
            <div>
                <label for="request_id">Request ID</label>
                <input id="request_id" name="request_id" value="<?= e((string) ($filters['request_id'] ?? '')) ?>" placeholder="Ex.: 661d2abc-1f2e3d4c5b6a7d8e">
            </div>
            <div>
                <label for="created_from">Data inicial</label>
                <input id="created_from" type="date" name="created_from" value="<?= e((string) ($filters['created_from'] ?? '')) ?>">
            </div>
            <div>
                <label for="created_to">Data final</label>
                <input id="created_to" type="date" name="created_to" value="<?= e((string) ($filters['created_to'] ?? '')) ?>">
            </div>
            <div>
                <label for="per_page">Itens por pagina</label>
                <select id="per_page" name="per_page">
                    <?php foreach ([20, 50, 100] as $size): ?>
                        <option value="<?= $size ?>"<?= $perPage === $size ? ' selected' : '' ?>><?= $size ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit">Filtrar</button>
            <a class="button" href="<?= e(base_url('admin/index.php?route=logs')) ?>">Limpar</a>
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
                <th>Request ID</th>
                <th>Contexto</th>
                <th>Nivel</th>
                <th>Mensagem</th>
                <th>Data</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= (int) $log['log_id'] ?></td>
                        <td><?= e((string) ($log['request_id'] ?? '-')) ?></td>
                        <td><?= e((string) ($log['context'] ?? '')) ?></td>
                        <td><?= e((string) ($log['level'] ?? '')) ?></td>
                        <td><?= e((string) ($log['message'] ?? '')) ?></td>
                        <td><?= e((string) ($log['created_at'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="muted">Nenhum log encontrado para os filtros aplicados.</td>
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

<section class="card">
    <h2>Ultimas linhas de log em arquivo</h2>
    <pre style="white-space:pre-wrap;background:#0f1720;color:#d5e2f1;border-radius:8px;padding:12px;max-height:340px;overflow:auto;"><?= e($file_log_tail ?? '') ?></pre>
</section>
