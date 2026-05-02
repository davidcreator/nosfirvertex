<?php
$templateCategory = strtolower(trim((string) ($resume['template_category'] ?? 'basico')));
$allowedCategories = ['basico', 'moderno', 'profissional', 'criativo', 'minimalista', 'coluna2575', 'coluna7525'];

if (!in_array($templateCategory, $allowedCategories, true)) {
    $templateCategory = 'basico';
}

$splitLines = static function (string $value): array {
    $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

    return array_values(array_filter(array_map('trim', $parts), static fn (string $line): bool => $line !== ''));
};

$normalizeTextBlock = static function (string $value) use ($splitLines): string {
    return implode("\n", $splitLines($value));
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

$summaryText = $normalizeTextBlock((string) ($resume['professional_summary'] ?? ''));
$objectiveText = $normalizeTextBlock((string) ($resume['objective'] ?? ''));

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
    $description = $normalizeTextBlock((string) ($item['description'] ?? ''));
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
    $description = $normalizeTextBlock((string) ($item['description'] ?? ''));
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

$hasMainColumnContent = $summaryText !== ''
    || $objectiveText !== ''
    || $experiences !== []
    || $educations !== []
    || $projects !== [];

$hasSideColumnContent = $skills !== []
    || $languages !== []
    || $certifications !== []
    || $courses !== []
    || $links !== [];

$isColumnTemplate = in_array($templateCategory, ['coluna2575', 'coluna7525'], true);
$useColumnLayout = $isColumnTemplate && $hasMainColumnContent && $hasSideColumnContent;
$isLeftWideColumn = $templateCategory === 'coluna7525';

$designOptions = is_array($resume['design_options'] ?? null) ? $resume['design_options'] : [];
$fontSize = (int) ($designOptions['font_size'] ?? 11);
if ($fontSize < 10 || $fontSize > 14) {
    $fontSize = 11;
}

$themeMap = [
    'basico' => ['accent' => '#0a66c2', 'soft' => '#f3f8fd', 'line' => '#dbe4ef', 'text' => '#1f2937', 'muted' => '#4b5563', 'font' => 'DejaVu Sans'],
    'moderno' => ['accent' => '#0f766e', 'soft' => '#f2fbf9', 'line' => '#d4ebe5', 'text' => '#1f2937', 'muted' => '#4b5563', 'font' => 'DejaVu Sans'],
    'profissional' => ['accent' => '#1f2933', 'soft' => '#f5f7fa', 'line' => '#dce3ec', 'text' => '#111827', 'muted' => '#4b5563', 'font' => 'DejaVu Serif'],
    'criativo' => ['accent' => '#a65012', 'soft' => '#fdf7f2', 'line' => '#ecdccc', 'text' => '#2f2620', 'muted' => '#6f5b4f', 'font' => 'DejaVu Sans'],
    'minimalista' => ['accent' => '#111827', 'soft' => '#f7f8fa', 'line' => '#e2e6ec', 'text' => '#111827', 'muted' => '#4b5563', 'font' => 'DejaVu Sans'],
    'coluna2575' => ['accent' => '#1f3b57', 'soft' => '#f1f6fb', 'line' => '#d3dfec', 'text' => '#142638', 'muted' => '#4b5f73', 'font' => 'DejaVu Sans'],
    'coluna7525' => ['accent' => '#245748', 'soft' => '#f2f8f5', 'line' => '#d0e2da', 'text' => '#17342b', 'muted' => '#496459', 'font' => 'DejaVu Sans'],
];

$theme = $themeMap[$templateCategory];
$theme['accent'] = $sanitizeHexColor((string) ($designOptions['accent_color'] ?? '')) ?? $theme['accent'];
$theme['soft'] = $sanitizeHexColor((string) ($designOptions['header_bg_color'] ?? '')) ?? $theme['soft'];
$theme['header_text'] = $sanitizeHexColor((string) ($designOptions['header_text_color'] ?? '')) ?? $theme['text'];
$theme['text'] = $sanitizeHexColor((string) ($designOptions['text_color'] ?? '')) ?? $theme['text'];

$resumeId = (int) ($resume['resume_id'] ?? 0);
$browserExportUrl = base_url('catalog/index.php?route=resume/export/browser/' . $resumeId);
$platformTargets = [
    [
        'name' => 'LinkedIn',
        'url' => 'https://www.linkedin.com/jobs/application-settings/',
        'note' => 'Carregue o PDF em Resumes and application data.',
    ],
    [
        'name' => 'CIEE',
        'url' => 'https://web.ciee.org.br/login/cadastro',
        'note' => 'Atualize o perfil e anexe o curriculo.',
    ],
    [
        'name' => 'Catho',
        'url' => 'https://www.catho.com.br/cadastro-candidato/',
        'note' => 'Cadastre o curriculo para candidaturas.',
    ],
    [
        'name' => 'Infojobs',
        'url' => 'https://www.infojobs.com.br/cadastrar-curriculo.aspx',
        'note' => 'Envie o CV para aplicar nas vagas.',
    ],
];

$renderMainSections = static function () use ($summaryText, $objectiveText, $experiences, $educations, $projects): void {
    if ($summaryText !== ''): ?>
        <section class="section">
            <h2 class="section-title">Resumo</h2>
            <p><?= nl2br(e($summaryText)) ?></p>
        </section>
    <?php endif;

    if ($objectiveText !== ''): ?>
        <section class="section">
            <h2 class="section-title">Objetivo</h2>
            <p><?= nl2br(e($objectiveText)) ?></p>
        </section>
    <?php endif;

    if ($experiences !== []): ?>
        <section class="section">
            <h2 class="section-title">Experiência</h2>
            <?php foreach ($experiences as $item): ?>
                <article class="item">
                    <p class="item-title"><?= e((string) $item['headline']) ?></p>
                    <?php if ($item['subtitle'] !== ''): ?>
                        <p class="item-subtitle"><?= e((string) $item['subtitle']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['period'] !== ''): ?>
                        <p class="item-period"><?= e((string) $item['period']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['bullets'] !== []): ?>
                        <ul>
                            <?php foreach ($item['bullets'] as $bullet): ?>
                                <li><?= e((string) $bullet) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif;

    if ($educations !== []): ?>
        <section class="section">
            <h2 class="section-title">Formação</h2>
            <?php foreach ($educations as $item): ?>
                <article class="item">
                    <p class="item-title"><?= e((string) $item['headline']) ?></p>
                    <?php if ($item['subtitle'] !== ''): ?>
                        <p class="item-subtitle"><?= e((string) $item['subtitle']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['period'] !== ''): ?>
                        <p class="item-period"><?= e((string) $item['period']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['description'] !== ''): ?>
                        <p><?= nl2br(e((string) $item['description'])) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif;

    if ($projects !== []): ?>
        <section class="section">
            <h2 class="section-title">Projetos</h2>
            <?php foreach ($projects as $item): ?>
                <article class="item">
                    <?php if ($item['name'] !== ''): ?>
                        <p class="item-title"><?= e((string) $item['name']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['role'] !== ''): ?>
                        <p class="item-subtitle"><?= e((string) $item['role']) ?></p>
                    <?php endif; ?>
                    <?php if ($item['description'] !== ''): ?>
                        <p><?= nl2br(e((string) $item['description'])) ?></p>
                    <?php endif; ?>
                    <?php if ($item['link'] !== ''): ?>
                        <p class="muted"><?= e((string) $item['link']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif;
};

$renderSideSections = static function () use ($skills, $languages, $certifications, $courses, $links): void {
    if ($skills !== []): ?>
        <section class="section">
            <h2 class="section-title">Habilidades</h2>
            <ul>
                <?php foreach ($skills as $item): ?>
                    <li><?= e((string) $item['skill']) ?><?= $item['level'] !== '' ? ' (' . e((string) $item['level']) . ')' : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif;

    if ($languages !== []): ?>
        <section class="section">
            <h2 class="section-title">Idiomas</h2>
            <ul>
                <?php foreach ($languages as $item): ?>
                    <li><?= e((string) $item['language']) ?><?= $item['level'] !== '' ? ' (' . e((string) $item['level']) . ')' : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif;

    if ($certifications !== []): ?>
        <section class="section">
            <h2 class="section-title">Certificações</h2>
            <ul>
                <?php foreach ($certifications as $item): ?>
                    <li><?= e((string) $item['title']) ?><?= $item['issuer'] !== '' ? ' - ' . e((string) $item['issuer']) : '' ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif;

    if ($courses !== []): ?>
        <section class="section">
            <h2 class="section-title">Cursos</h2>
            <ul>
                <?php foreach ($courses as $item): ?>
                    <li>
                        <?= e((string) $item['name']) ?>
                        <?= $item['institution'] !== '' ? ' - ' . e((string) $item['institution']) : '' ?>
                        <?= $item['year'] !== '' ? ' (' . e((string) $item['year']) . ')' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif;

    if ($links !== []): ?>
        <section class="section">
            <h2 class="section-title">Links</h2>
            <ul>
                <?php foreach ($links as $item): ?>
                    <li><?= $item['label'] !== '' ? e((string) $item['label']) . ': ' : '' ?><?= e((string) $item['url']) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif;
};

$renderPlatformSection = static function () use ($platformTargets, $browserExportUrl): void {
    if ($platformTargets === []) {
        return;
    }
    ?>
    <section class="section section-full">
        <h2 class="section-title">Envio para plataformas</h2>
        <ul class="platform-list">
            <?php foreach ($platformTargets as $target): ?>
                <li>
                    <strong><?= e((string) ($target['name'] ?? '')) ?>:</strong>
                    <?= e((string) ($target['note'] ?? '')) ?>
                    <a class="platform-link" href="<?= e((string) ($target['url'] ?? '')) ?>"><?= e((string) ($target['url'] ?? '')) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="muted">Link web do curriculo: <?= e($browserExportUrl) ?></p>
    </section>
    <?php
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <?php $exportPdfCssTemplate = __DIR__ . '/../css/export-pdf.php'; ?>
    <style>
<?php if (is_file($exportPdfCssTemplate)): ?>
<?php include $exportPdfCssTemplate; ?>
<?php endif; ?>
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <h1 class="name"><?= e($displayName) ?></h1>
            <p class="headline"><?= e((string) ($resume['title'] ?? 'Currículo profissional')) ?></p>
            <p class="meta">Gerado em <?= e(date('d/m/Y H:i')) ?></p>
            <?php if (!empty($contactItems)): ?>
                <p class="contact"><?= e(implode(' | ', $contactItems)) ?></p>
            <?php endif; ?>
        </div>

        <div class="body">
            <?php if ($hasRenderableSections): ?>
                <?php if ($useColumnLayout): ?>
                    <div class="columns <?= $isLeftWideColumn ? 'columns-75-25' : 'columns-25-75' ?>">
                        <?php if ($isLeftWideColumn): ?>
                            <div class="column column-main">
                                <?php $renderMainSections(); ?>
                            </div>
                            <div class="column column-side">
                                <?php $renderSideSections(); ?>
                            </div>
                        <?php else: ?>
                            <div class="column column-side">
                                <?php $renderSideSections(); ?>
                            </div>
                            <div class="column column-main">
                                <?php $renderMainSections(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php $renderMainSections(); ?>
                    <?php $renderSideSections(); ?>
                <?php endif; ?>

                <?php $renderPlatformSection(); ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

