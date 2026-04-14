<?php
$templateCategory = strtolower(trim((string) ($resume['template_category'] ?? 'basico')));
$allowedCategories = ['basico', 'moderno', 'profissional', 'criativo', 'minimalista'];

if (!in_array($templateCategory, $allowedCategories, true)) {
    $templateCategory = 'basico';
}

$splitLines = static function (string $value): array {
    $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

    return array_values(array_filter(array_map('trim', $parts), static fn (string $line): bool => $line !== ''));
};

$toBullets = static function (string $value): array {
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($value));
    $segments = preg_split('/\n|\s*;\s*/', $normalized) ?: [];

    return array_values(array_filter(array_map('trim', $segments), static fn (string $segment): bool => $segment !== ''));
};

$sanitizeHexColor = static function (string $value): string|null {
    $value = trim($value);
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
        return null;
    }

    return strtolower($value);
};

$personalLines = $splitLines((string) ($resume['personal_data'] ?? ''));
$headlineParts = [];
if (!empty($personalLines)) {
    $headlineParts = array_values(array_filter(array_map('trim', explode('|', $personalLines[0])), static fn (string $value): bool => $value !== ''));
}

$displayName = $headlineParts[0] ?? trim((string) ($resume['title'] ?? 'Currículo Profissional'));
if ($displayName === '') {
    $displayName = 'Currículo Profissional';
}

$contactItems = [];
if (count($headlineParts) > 1) {
    $contactItems = array_slice($headlineParts, 1);
}
if (count($personalLines) > 1) {
    foreach (array_slice($personalLines, 1) as $line) {
        $contactItems[] = $line;
    }
}

$summaryText = trim((string) ($resume['professional_summary'] ?? ''));
$objectiveText = trim((string) ($resume['objective'] ?? ''));

$experiencesRaw = is_array($resume['experiences'] ?? null) ? $resume['experiences'] : [];
$educationsRaw = is_array($resume['educations'] ?? null) ? $resume['educations'] : [];
$coursesRaw = is_array($resume['courses'] ?? null) ? $resume['courses'] : [];
$skillsRaw = is_array($resume['skills'] ?? null) ? $resume['skills'] : [];
$languagesRaw = is_array($resume['languages'] ?? null) ? $resume['languages'] : [];
$certificationsRaw = is_array($resume['certifications'] ?? null) ? $resume['certifications'] : [];
$projectsRaw = is_array($resume['projects'] ?? null) ? $resume['projects'] : [];
$linksRaw = is_array($resume['links'] ?? null) ? $resume['links'] : [];

$experiences = [];
foreach ($experiencesRaw as $item) {
    $company = trim((string) ($item['company'] ?? ''));
    $role = trim((string) ($item['role'] ?? ''));
    $start = trim((string) ($item['start_period'] ?? ''));
    $end = trim((string) ($item['end_period'] ?? ''));
    $bullets = $toBullets((string) ($item['description'] ?? ''));
    $period = trim($start . (($start !== '' || $end !== '') ? ' - ' : '') . ($end !== '' ? $end : ($start !== '' ? 'Atual' : '')));

    if ($company === '' && $role === '' && $period === '' && $bullets === []) {
        continue;
    }

    $headline = $role !== '' ? $role : $company;
    $subtitle = ($role !== '' && $company !== '') ? $company : '';

    $experiences[] = [
        'headline' => $headline,
        'subtitle' => $subtitle,
        'period' => $period,
        'bullets' => $bullets,
    ];
}

$educations = [];
foreach ($educationsRaw as $item) {
    $institution = trim((string) ($item['institution'] ?? ''));
    $degree = trim((string) ($item['degree'] ?? ''));
    $start = trim((string) ($item['start_period'] ?? ''));
    $end = trim((string) ($item['end_period'] ?? ''));
    $description = trim((string) ($item['description'] ?? ''));
    $period = trim($start . (($start !== '' || $end !== '') ? ' - ' : '') . $end);

    if ($institution === '' && $degree === '' && $period === '' && $description === '') {
        continue;
    }

    $headline = $degree !== '' ? $degree : $institution;
    $subtitle = ($degree !== '' && $institution !== '') ? $institution : '';

    $educations[] = [
        'headline' => $headline,
        'subtitle' => $subtitle,
        'period' => $period,
        'description' => $description,
    ];
}

$courses = [];
foreach ($coursesRaw as $item) {
    $name = trim((string) ($item['name'] ?? ''));
    if ($name === '') {
        continue;
    }

    $courses[] = [
        'name' => $name,
        'institution' => trim((string) ($item['institution'] ?? '')),
        'year' => trim((string) ($item['completion_year'] ?? '')),
    ];
}

$skills = [];
foreach ($skillsRaw as $item) {
    $skill = trim((string) ($item['skill'] ?? ''));
    if ($skill === '') {
        continue;
    }

    $skills[] = [
        'skill' => $skill,
        'level' => trim((string) ($item['level'] ?? '')),
    ];
}

$languages = [];
foreach ($languagesRaw as $item) {
    $language = trim((string) ($item['language'] ?? ''));
    if ($language === '') {
        continue;
    }

    $languages[] = [
        'language' => $language,
        'level' => trim((string) ($item['level'] ?? '')),
    ];
}

$certifications = [];
foreach ($certificationsRaw as $item) {
    $title = trim((string) ($item['title'] ?? ''));
    if ($title === '') {
        continue;
    }

    $certifications[] = [
        'title' => $title,
        'issuer' => trim((string) ($item['issuer'] ?? '')),
    ];
}

$projects = [];
foreach ($projectsRaw as $item) {
    $name = trim((string) ($item['name'] ?? ''));
    $role = trim((string) ($item['role'] ?? ''));
    $description = trim((string) ($item['description'] ?? ''));
    $link = trim((string) ($item['project_link'] ?? ''));

    if ($name === '' && $role === '' && $description === '' && $link === '') {
        continue;
    }

    $projects[] = [
        'name' => $name,
        'role' => $role,
        'description' => $description,
        'link' => $link,
    ];
}

$links = [];
foreach ($linksRaw as $item) {
    $url = trim((string) ($item['url'] ?? ''));
    if ($url === '') {
        continue;
    }

    $links[] = [
        'label' => trim((string) ($item['label'] ?? '')),
        'url' => $url,
    ];
}

$hasRenderableSections = $summaryText !== ''
    || $objectiveText !== ''
    || $experiences !== []
    || $educations !== []
    || $skills !== []
    || $languages !== []
    || $certifications !== []
    || $courses !== []
    || $projects !== []
    || $links !== [];

$designOptions = is_array($resume['design_options'] ?? null) ? $resume['design_options'] : [];
$fontSize = (int) ($designOptions['font_size'] ?? 11);
if ($fontSize < 10 || $fontSize > 14) {
    $fontSize = 11;
}

$accentColor = $sanitizeHexColor((string) ($designOptions['accent_color'] ?? '')) ?? '#0a66c2';
$headerBgColor = $sanitizeHexColor((string) ($designOptions['header_bg_color'] ?? '')) ?? '#f3f8fd';
$textColor = $sanitizeHexColor((string) ($designOptions['text_color'] ?? '')) ?? '#1f2937';

$fontMap = [
    'basico' => '"Calibri", "Segoe UI", sans-serif',
    'moderno' => '"Trebuchet MS", "Segoe UI", sans-serif',
    'profissional' => '"Cambria", "Times New Roman", serif',
    'criativo' => '"Gill Sans", "Trebuchet MS", sans-serif',
    'minimalista' => '"Arial Narrow", "Segoe UI", sans-serif',
];
$fontBody = $fontMap[$templateCategory] ?? $fontMap['basico'];
?>
<style>
    .resume-linkedin {
        --accent: <?= e($accentColor) ?>;
        --header-bg: <?= e($headerBgColor) ?>;
        --text-main: <?= e($textColor) ?>;
        --text-muted: #4b5563;
        --line: #dbe4ef;
        --paper: #ffffff;
        --base-size: <?= (int) $fontSize ?>px;
        --font-body: <?= $fontBody ?>;
    }

    .resume-actions {
        margin-bottom: 12px;
    }

    .resume-paper {
        max-width: 900px;
        margin: 0 auto;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
        color: var(--text-main);
        font-family: var(--font-body);
        font-size: var(--base-size);
        line-height: 1.5;
        overflow: hidden;
    }

    .resume-header {
        background: var(--header-bg);
        border-bottom: 2px solid var(--accent);
        padding: 20px 22px 16px;
    }

    .resume-name {
        margin: 0;
        font-size: clamp(1.8rem, 2.8vw, 2.2rem);
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: 0.01em;
    }

    .resume-headline {
        margin: 6px 0 0;
        color: var(--text-main);
        font-size: 1rem;
    }

    .resume-contact {
        margin: 8px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        color: var(--text-muted);
        font-size: 0.93rem;
    }

    .resume-body {
        padding: 20px 22px;
    }

    .resume-section {
        margin-bottom: 16px;
    }

    .resume-section:last-child {
        margin-bottom: 0;
    }

    .resume-section-title {
        margin: 0 0 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid var(--line);
        color: var(--accent);
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    .resume-text {
        margin: 0;
        white-space: normal;
    }

    .resume-item {
        margin-bottom: 10px;
    }

    .resume-item:last-child {
        margin-bottom: 0;
    }

    .resume-item-header {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .resume-item-title {
        margin: 0;
        font-weight: 700;
        color: var(--text-main);
    }

    .resume-item-subtitle {
        margin: 2px 0 0;
        color: var(--text-muted);
        font-size: 0.95em;
    }

    .resume-item-period {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.9em;
        white-space: nowrap;
    }

    .resume-list,
    .resume-bullets {
        margin: 0;
        padding-left: 18px;
    }

    .resume-list li,
    .resume-bullets li {
        margin-bottom: 4px;
    }

    .resume-list li:last-child,
    .resume-bullets li:last-child {
        margin-bottom: 0;
    }

    .resume-link {
        color: var(--accent);
        text-decoration: none;
        word-break: break-word;
    }

    .resume-link:hover {
        text-decoration: underline;
    }

    .resume-empty-state {
        border: 1px dashed var(--line);
        border-radius: 10px;
        padding: 12px;
        color: var(--text-muted);
        background: color-mix(in srgb, var(--paper) 88%, #f3f8fd);
    }

    .resume-footer-note {
        margin-top: 10px;
        color: var(--text-muted);
        font-size: 0.82em;
    }

    @media (max-width: 720px) {
        .resume-header {
            padding: 16px;
        }

        .resume-body {
            padding: 16px;
        }
    }
</style>

<section class="card resume-linkedin">
    <div class="grid cols-2 resume-actions">
        <a class="button" href="<?= e(base_url('catalog/index.php?route=resume/edit/' . (int) ($resume['resume_id'] ?? 0))) ?>">Editar</a>
        <a class="button" href="<?= e(base_url('catalog/index.php?route=resume/export/browser/' . (int) ($resume['resume_id'] ?? 0))) ?>" target="_blank" rel="noopener noreferrer">Visualizar no navegador</a>
        <a class="button primary" href="<?= e(base_url('catalog/index.php?route=resume/export/pdf/' . (int) ($resume['resume_id'] ?? 0))) ?>">Exportar PDF</a>
        <a class="button" href="<?= e(base_url('catalog/index.php?route=resume/export/json/' . (int) ($resume['resume_id'] ?? 0))) ?>">Exportar JSON</a>
        <form method="post" action="<?= e(base_url('catalog/index.php?route=resume/delete/' . (int) ($resume['resume_id'] ?? 0))) ?>" onsubmit="return confirm('Excluir este currículo?');">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <button class="button warn" type="submit">Excluir</button>
        </form>
    </div>

    <article class="resume-paper">
        <header class="resume-header">
            <h1 class="resume-name"><?= e($displayName) ?></h1>
            <p class="resume-headline"><?= e((string) ($resume['title'] ?? 'Currículo profissional')) ?></p>
            <?php if (!empty($contactItems)): ?>
                <ul class="resume-contact">
                    <?php foreach ($contactItems as $contact): ?>
                        <li><?= e((string) $contact) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </header>

        <div class="resume-body">
            <?php if (!$hasRenderableSections): ?>
                <div class="resume-empty-state">
                    Nenhuma seção foi preenchida ainda. Volte ao formulário e complete os campos para gerar o currículo final.
                </div>
            <?php endif; ?>

            <?php if ($summaryText !== ''): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Resumo</h2>
                    <p class="resume-text"><?= nl2br(e($summaryText)) ?></p>
                </section>
            <?php endif; ?>

            <?php if ($objectiveText !== ''): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Objetivo</h2>
                    <p class="resume-text"><?= nl2br(e($objectiveText)) ?></p>
                </section>
            <?php endif; ?>

            <?php if ($experiences !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Experiência</h2>
                    <?php foreach ($experiences as $item): ?>
                        <article class="resume-item">
                            <div class="resume-item-header">
                                <div>
                                    <p class="resume-item-title"><?= e((string) $item['headline']) ?></p>
                                    <?php if ($item['subtitle'] !== ''): ?>
                                        <p class="resume-item-subtitle"><?= e((string) $item['subtitle']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['period'] !== ''): ?>
                                    <p class="resume-item-period"><?= e((string) $item['period']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['bullets'] !== []): ?>
                                <ul class="resume-bullets">
                                    <?php foreach ($item['bullets'] as $bullet): ?>
                                        <li><?= e((string) $bullet) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if ($educations !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Formação</h2>
                    <?php foreach ($educations as $item): ?>
                        <article class="resume-item">
                            <div class="resume-item-header">
                                <div>
                                    <p class="resume-item-title"><?= e((string) $item['headline']) ?></p>
                                    <?php if ($item['subtitle'] !== ''): ?>
                                        <p class="resume-item-subtitle"><?= e((string) $item['subtitle']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($item['period'] !== ''): ?>
                                    <p class="resume-item-period"><?= e((string) $item['period']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['description'] !== ''): ?>
                                <p class="resume-text"><?= nl2br(e((string) $item['description'])) ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if ($skills !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Habilidades</h2>
                    <ul class="resume-list">
                        <?php foreach ($skills as $item): ?>
                            <li><?= e((string) $item['skill']) ?><?= $item['level'] !== '' ? ' (' . e((string) $item['level']) . ')' : '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($languages !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Idiomas</h2>
                    <ul class="resume-list">
                        <?php foreach ($languages as $item): ?>
                            <li><?= e((string) $item['language']) ?><?= $item['level'] !== '' ? ' (' . e((string) $item['level']) . ')' : '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($certifications !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Certificações</h2>
                    <ul class="resume-list">
                        <?php foreach ($certifications as $item): ?>
                            <li><?= e((string) $item['title']) ?><?= $item['issuer'] !== '' ? ' - ' . e((string) $item['issuer']) : '' ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($courses !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Cursos</h2>
                    <ul class="resume-list">
                        <?php foreach ($courses as $item): ?>
                            <li>
                                <?= e((string) $item['name']) ?>
                                <?= $item['institution'] !== '' ? ' - ' . e((string) $item['institution']) : '' ?>
                                <?= $item['year'] !== '' ? ' (' . e((string) $item['year']) . ')' : '' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($projects !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Projetos</h2>
                    <?php foreach ($projects as $item): ?>
                        <article class="resume-item">
                            <?php if ($item['name'] !== ''): ?>
                                <p class="resume-item-title"><?= e((string) $item['name']) ?></p>
                            <?php endif; ?>
                            <?php if ($item['role'] !== ''): ?>
                                <p class="resume-item-subtitle"><?= e((string) $item['role']) ?></p>
                            <?php endif; ?>
                            <?php if ($item['description'] !== ''): ?>
                                <p class="resume-text"><?= nl2br(e((string) $item['description'])) ?></p>
                            <?php endif; ?>
                            <?php if ($item['link'] !== ''): ?>
                                <p class="resume-text"><a class="resume-link" href="<?= e((string) $item['link']) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $item['link']) ?></a></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <?php if ($links !== []): ?>
                <section class="resume-section">
                    <h2 class="resume-section-title">Links</h2>
                    <ul class="resume-list">
                        <?php foreach ($links as $item): ?>
                            <li>
                                <?php if ($item['label'] !== ''): ?>
                                    <?= e((string) $item['label']) ?>:
                                <?php endif; ?>
                                <a class="resume-link" href="<?= e((string) $item['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $item['url']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php if ($hasRenderableSections): ?>
                <p class="resume-footer-note">Currículo exibido apenas com os campos preenchidos, sem seções vazias.</p>
            <?php endif; ?>
        </div>
    </article>
</section>
