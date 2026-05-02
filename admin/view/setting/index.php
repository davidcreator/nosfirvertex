<?php
$settingsMap = is_array($settings_map ?? null) ? $settings_map : [];
$donationDefaults = is_array($donation_defaults ?? null) ? $donation_defaults : [];
$donationKeys = array_keys($donationDefaults);

$readSetting = static function (string $key) use ($settingsMap, $donationDefaults): string {
    if (array_key_exists($key, $settingsMap)) {
        return (string) $settingsMap[$key];
    }

    return (string) ($donationDefaults[$key] ?? '');
};
?>

<section class="card">
    <h1><?= e(lang('Configurações do sistema')) ?></h1>
    <p class="muted"><?= e(lang('Gerencie parâmetros globais e a área de doações do catálogo.')) ?></p>

    <form method="post" action="<?= e(base_url('admin/index.php?route=settings')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

        <section class="form-section">
            <h2><?= e(lang('Área de doações')) ?></h2>
            <p class="muted"><?= e(lang('Campos vazios não serão exibidos para o usuário final.')) ?></p>

            <div class="grid cols-3">
                <?php $enabledValue = $readSetting('donation_enabled'); ?>
                <div class="field-card">
                    <label for="donation_enabled"><?= e(lang('Doações ativas')) ?></label>
                    <select id="donation_enabled" name="donation_enabled">
                        <option value="1"<?= $enabledValue !== '0' ? ' selected' : '' ?>><?= e(lang('Sim')) ?></option>
                        <option value="0"<?= $enabledValue === '0' ? ' selected' : '' ?>><?= e(lang('Não')) ?></option>
                    </select>
                </div>

                <div class="field-card">
                    <label for="donation_title"><?= e(lang('Título')) ?></label>
                    <input id="donation_title" name="donation_title" value="<?= e($readSetting('donation_title')) ?>">
                </div>

                <div class="field-card">
                    <label for="donation_pix_beneficiary"><?= e(lang('Nome beneficiário')) ?></label>
                    <input id="donation_pix_beneficiary" name="donation_pix_beneficiary" value="<?= e($readSetting('donation_pix_beneficiary')) ?>">
                </div>

                <div class="field-card">
                    <label for="donation_pix_key"><?= e(lang('Chave PIX')) ?></label>
                    <input id="donation_pix_key" name="donation_pix_key" value="<?= e($readSetting('donation_pix_key')) ?>">
                </div>

                <div class="field-card">
                    <label for="donation_paypal_url"><?= e(lang('Link de pagamento (PayPal ou similar)')) ?></label>
                    <input id="donation_paypal_url" name="donation_paypal_url" value="<?= e($readSetting('donation_paypal_url')) ?>">
                </div>

                <div class="field-card">
                    <label for="donation_qr_image"><?= e(lang('Imagem QR (URL ou caminho relativo)')) ?></label>
                    <input id="donation_qr_image" name="donation_qr_image" value="<?= e($readSetting('donation_qr_image')) ?>">
                </div>
            </div>

            <div class="grid">
                <div class="field-card">
                    <label for="donation_message"><?= e(lang('Mensagem de apoio')) ?></label>
                    <textarea id="donation_message" name="donation_message"><?= e($readSetting('donation_message')) ?></textarea>
                </div>

                <div class="field-card">
                    <label for="donation_goal_text"><?= e(lang('Destino das contribuições')) ?></label>
                    <textarea id="donation_goal_text" name="donation_goal_text"><?= e($readSetting('donation_goal_text')) ?></textarea>
                </div>

                <div class="field-card">
                    <label for="donation_bank_transfer"><?= e(lang('Dados de transferência bancária (opcional)')) ?></label>
                    <textarea id="donation_bank_transfer" name="donation_bank_transfer"><?= e($readSetting('donation_bank_transfer')) ?></textarea>
                </div>

                <div class="field-card">
                    <label for="donation_thanks_message"><?= e(lang('Mensagem de agradecimento')) ?></label>
                    <textarea id="donation_thanks_message" name="donation_thanks_message"><?= e($readSetting('donation_thanks_message')) ?></textarea>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h2><?= e(lang('Outras configurações')) ?></h2>
            <div class="grid cols-3">
                <?php foreach ($settings as $setting): ?>
                    <?php $settingKey = (string) ($setting['key'] ?? ''); ?>
                    <?php if (in_array($settingKey, $donationKeys, true)): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <div class="field-card">
                        <label for="setting_<?= (int) $setting['setting_id'] ?>"><?= e($settingKey) ?></label>
                        <input id="setting_<?= (int) $setting['setting_id'] ?>" name="<?= e($settingKey) ?>" value="<?= e((string) ($setting['value'] ?? '')) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <button type="submit"><?= e(lang('Salvar configurações')) ?></button>
    </form>
</section>
