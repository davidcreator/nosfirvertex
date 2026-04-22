<?php
$donationEnabled = !empty($donation_enabled);
$title = trim((string) ($donation_title ?? 'Apoie o Vertex'));
$message = trim((string) ($donation_message ?? ''));
$goalText = trim((string) ($donation_goal_text ?? ''));
$pixKey = trim((string) ($donation_pix_key ?? ''));
$pixBeneficiary = trim((string) ($donation_pix_beneficiary ?? ''));
$paypalUrl = trim((string) ($donation_paypal_url ?? ''));
$bankTransfer = trim((string) ($donation_bank_transfer ?? ''));
$qrImage = trim((string) ($donation_qr_image ?? ''));
$thanksMessage = trim((string) ($donation_thanks_message ?? ''));

$hasPix = $pixKey !== '';
$hasBank = $bankTransfer !== '';
$hasQr = $qrImage !== '';

$paypalUrlSafe = '';
if ($paypalUrl !== '' && preg_match('#^https?://#i', $paypalUrl) === 1) {
    $paypalUrlSafe = $paypalUrl;
}

$hasPaypal = $paypalUrlSafe !== '';

$qrImageUrl = '';
if ($hasQr) {
    if (preg_match('#^https?://#i', $qrImage) === 1) {
        $qrImageUrl = $qrImage;
    } else {
        $qrImageUrl = base_url(ltrim($qrImage, '/'));
    }
}
?>
<style>
    .donation-key {
        font-family: "Consolas", "Courier New", monospace;
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 10px;
        background: color-mix(in srgb, var(--surface) 90%, #eef6ff);
        word-break: break-all;
        margin-bottom: 8px;
    }

    .donation-qr {
        width: 100%;
        max-width: 240px;
        border: 1px solid var(--border);
        border-radius: 10px;
        display: block;
        margin-top: 8px;
    }

    .donation-soft {
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 12px;
        background: color-mix(in srgb, var(--surface) 92%, #f0f8f7);
    }
</style>

<section class="card">
    <h1><i class="fa-solid fa-hand-holding-heart"></i> <?= e($title !== '' ? $title : 'Apoie o Vertex') ?></h1>
    <p class="muted">
        <?= e($message !== '' ? $message : 'Se você quiser, pode contribuir voluntariamente para manter o projeto gratuito e evoluindo.') ?>
    </p>
</section>

<?php if (!$donationEnabled): ?>
    <section class="card">
        <h2><i class="fa-solid fa-pause-circle"></i> Doações temporariamente indisponíveis</h2>
        <p class="muted">No momento, a área de contribuições está desativada. Obrigado pelo interesse em apoiar.</p>
    </section>
<?php else: ?>
    <?php if (!$hasPix && !$hasPaypal && !$hasBank): ?>
        <section class="card">
            <h2><i class="fa-solid fa-circle-info"></i> Configuração pendente</h2>
            <p class="muted">Nenhum método de doação foi configurado ainda. Volte em breve para contribuir.</p>
        </section>
    <?php else: ?>
        <section class="grid cols-2">
            <?php if ($hasPix): ?>
                <article class="card">
                    <h2><i class="fa-solid fa-qrcode"></i> Doação via PIX</h2>
                    <p class="muted">Beneficiário: <?= e($pixBeneficiary !== '' ? $pixBeneficiary : 'Vertex') ?></p>
                    <div class="donation-key" id="pixKeyValue"><?= e($pixKey) ?></div>
                    <button class="button" type="button" data-copy-target="pixKeyValue">Copiar chave PIX</button>
                    <?php if ($qrImageUrl !== ''): ?>
                        <img class="donation-qr" src="<?= e($qrImageUrl) ?>" alt="QR Code PIX">
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <?php if ($hasPaypal || $hasBank): ?>
                <article class="card">
                    <h2><i class="fa-solid fa-wallet"></i> Outras formas de contribuir</h2>
                    <?php if ($hasPaypal): ?>
                        <p><a class="button primary" href="<?= e($paypalUrlSafe) ?>" target="_blank" rel="noopener noreferrer">Contribuir online</a></p>
                    <?php endif; ?>
                    <?php if ($hasBank): ?>
                        <div class="donation-soft">
                            <strong>Transferência bancária</strong>
                            <p class="muted" style="margin-top:6px;"><?= nl2br(e($bankTransfer)) ?></p>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="card">
        <h2><i class="fa-solid fa-bullseye"></i> Para onde vai sua contribuição</h2>
        <p class="muted">
            <?= e($goalText !== '' ? $goalText : 'Sua doação ajuda a manter infraestrutura, suporte e melhorias contínuas na plataforma.') ?>
        </p>
        <p><?= e($thanksMessage !== '' ? $thanksMessage : 'Muito obrigado pelo apoio.') ?></p>
    </section>
<?php endif; ?>

<script>
(() => {
    const buttons = document.querySelectorAll('[data-copy-target]');
    if (!buttons.length) return;

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const targetId = button.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) return;

            const text = (target.textContent || '').trim();
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                const original = button.textContent;
                button.textContent = 'Chave copiada';
                setTimeout(() => {
                    button.textContent = original;
                }, 1800);
            } catch (error) {
                button.textContent = 'Não foi possível copiar';
            }
        });
    });
})();
</script>
