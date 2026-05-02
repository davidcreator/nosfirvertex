<link rel="stylesheet" href="<?= e(base_url('catalog/view/css/home-entry.css')) ?>">

<section class="vertex-entry">
    <div class="vertex-entry-shell">
        <div class="vertex-entry-grid">
            <section class="vertex-entry-panel">
                <div class="vertex-entry-brand">
                    <img src="<?= e(base_url('image/vertex_logo.png')) ?>" alt="Vertex">
                    <strong>Vertex</strong>
                </div>                
                <h1><?= e(lang('home.entry.title')) ?></h1>
                <p class="vertex-entry-lead"><?= e(lang('home.entry.description')) ?></p>

                <ul class="vertex-entry-list">
                    <li>
                        <strong><?= e(lang('home.entry.features.smart_form.title')) ?></strong>
                        <?= e(lang('home.entry.features.smart_form.text')) ?>
                    </li>
                    <li>
                        <strong><?= e(lang('home.entry.features.share_export.title')) ?></strong>
                        <?= e(lang('home.entry.features.share_export.text')) ?>
                    </li>
                    <li>
                        <strong><?= e(lang('home.entry.features.job_platforms.title')) ?></strong>
                        <?= e(lang('home.entry.features.job_platforms.text')) ?>
                    </li>
                    <li>
                        <strong><?= e(lang('home.entry.features.anywhere.title')) ?></strong>
                        <?= e(lang('home.entry.features.anywhere.text')) ?>
                    </li>
                </ul>
<?php
                $panelLocaleOptions = is_array($available_locales ?? null) ? $available_locales : available_locales();
                $panelActiveLocale = (string) ($current_locale ?? current_locale());
                ?>
                <div class="vertex-entry-language-selector">
                    <?php foreach ($panelLocaleOptions as $localeCode): ?>
                        <?php $isActiveLocale = $panelActiveLocale === (string) $localeCode; ?>
                        <a
                            class="vertex-entry-language-option<?= $isActiveLocale ? ' is-active' : '' ?>"
                            href="<?= e(locale_switch_url((string) $localeCode)) ?>"
                        >
                            <?= e(strtoupper((string) $localeCode)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="vertex-entry-login">
                <h2><?= e(lang('home.login.title')) ?></h2>
                <p><?= e(lang('home.login.description')) ?></p>

                <?php if (!empty($auth_user)): ?>
                    <div class="vertex-entry-connected">
                        <p><?= e(lang('home.login.connected_description')) ?></p>
                        <a class="button" href="<?= e(base_url('catalog/index.php?route=dashboard')) ?>"><?= e(lang('home.login.open_dashboard')) ?></a>
                        <a class="button secondary" href="<?= e(base_url('catalog/index.php?route=resume/create')) ?>"><?= e(lang('home.login.create_resume')) ?></a>
                        <form method="post" action="<?= e(base_url('catalog/index.php?route=logout')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                            <button type="submit"><?= e(lang('home.login.sign_out')) ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <form class="vertex-entry-form" method="post" action="<?= e(base_url('catalog/index.php?route=login')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                        <label for="entry-email"><?= e(lang('home.login.email')) ?></label>
                        <input id="entry-email" name="email" type="email" autocomplete="username" required>

                        <label for="entry-password"><?= e(lang('home.login.password')) ?></label>
                        <input id="entry-password" name="password" type="password" autocomplete="current-password" required>

                        <button type="submit" class="vertex-entry-submit"><?= e(lang('home.login.submit')) ?></button>
                    </form>
                    <div class="vertex-entry-login-links">
                        <a class="create-link" href="<?= e(base_url('catalog/index.php?route=register')) ?>"><?= e(lang('home.login.create_account')) ?></a>
                        <a class="forgot-link" href="<?= e(base_url('catalog/index.php?route=password/forgot')) ?>"><?= e(lang('home.login.forgot_password')) ?></a>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>
