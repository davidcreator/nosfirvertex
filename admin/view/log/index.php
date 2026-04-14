<section class="card">
    <h1>Logs do sistema</h1>
    <p class="muted">Monitoramento de eventos e falhas básicas.</p>

    <div style="overflow:auto;">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Contexto</th>
                <th>Nível</th>
                <th>Mensagem</th>
                <th>Data</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= (int) $log['log_id'] ?></td>
                    <td><?= e($log['context']) ?></td>
                    <td><?= e($log['level']) ?></td>
                    <td><?= e($log['message']) ?></td>
                    <td><?= e((string) ($log['created_at'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <h2>Últimas linhas de log em arquivo</h2>
    <pre style="white-space:pre-wrap;background:#0f1720;color:#d5e2f1;border-radius:8px;padding:12px;max-height:340px;overflow:auto;"><?= e($file_log_tail ?? '') ?></pre>
</section>
