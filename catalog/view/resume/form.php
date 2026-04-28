<?php
$isEdit = ($mode ?? 'create') === 'edit';
$formData = is_array($form ?? null) ? $form : [];
$value = static fn (string $key, string $default = ''): string => (string) ($formData[$key] ?? $default);
?>
<style>
    .assistant-board {
        border: 1px solid var(--border);
        background: color-mix(in srgb, var(--surface) 92%, #eaf2ff);
    }

    .assistant-progress-track {
        width: 100%;
        height: 12px;
        border-radius: 999px;
        background: color-mix(in srgb, var(--surface) 85%, #d9e5fb);
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .assistant-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #0a66c2, #0f7ae8);
        transition: width .25s ease;
    }

    .assistant-progress-text {
        margin: 8px 0 0;
        font-weight: 600;
    }

    .assistant-checklist {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .assistant-checklist li {
        margin-bottom: 4px;
        color: var(--muted);
    }

    .assistant-checklist li.done {
        color: var(--success);
        font-weight: 600;
    }

    .assistant-tip {
        margin-top: 12px;
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 10px;
        background: color-mix(in srgb, var(--surface) 93%, #f0f7ff);
    }

    .assistant-anchors {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .assistant-anchor {
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 6px 10px;
        color: var(--text);
        background: var(--surface);
        font-size: 13px;
    }

    .field-meta {
        margin-top: -4px;
        margin-bottom: 8px;
        color: var(--muted);
        font-size: 12px;
    }

    .field-actions {
        display: flex;
        gap: 8px;
        margin-bottom: 6px;
        flex-wrap: wrap;
    }

    .field-actions button {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        color: var(--text);
        padding: 6px 10px;
        font-size: 12px;
        cursor: pointer;
    }

    .resume-design-preview {
        --preview-accent: #0a66c2;
        --preview-header-bg: #f3f8fd;
        --preview-header-text: #1f2937;
        --preview-body-text: #1f2937;
        --preview-font-size: 11px;
        --preview-font: "Calibri", "Segoe UI", sans-serif;
        margin-top: 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        background: var(--surface);
        color: var(--preview-body-text);
        font-family: var(--preview-font);
        font-size: var(--preview-font-size);
    }

    .resume-design-preview-header {
        background: var(--preview-header-bg);
        color: var(--preview-header-text);
        border-bottom: 2px solid var(--preview-accent);
        padding: 12px 14px;
    }

    .resume-design-preview-name {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
    }

    .resume-design-preview-headline {
        margin: 4px 0 0;
    }

    .resume-design-preview-meta {
        margin: 6px 0 0;
        opacity: .84;
        font-size: 12px;
    }

    .resume-design-preview-body {
        padding: 12px 14px;
    }

    .resume-design-preview-title {
        margin: 0 0 6px;
        color: var(--preview-accent);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: .08em;
        font-weight: 700;
        border-bottom: 1px solid color-mix(in srgb, var(--preview-accent) 30%, #dbe4ef);
        padding-bottom: 4px;
    }

    .resume-design-preview-text {
        margin: 0;
        color: var(--preview-body-text);
    }

    .resume-design-preview[data-template-category="moderno"] {
        border-radius: 14px;
    }

    .resume-design-preview[data-template-category="profissional"] .resume-design-preview-header {
        border-bottom-width: 3px;
    }

    .resume-design-preview[data-template-category="criativo"] .resume-design-preview-header {
        background-image: linear-gradient(135deg, color-mix(in srgb, var(--preview-header-bg) 85%, #fff), var(--preview-header-bg));
    }

    .resume-design-preview[data-template-category="minimalista"] {
        border-radius: 0;
    }

    .resume-design-preview[data-template-category="coluna2575"] .resume-design-preview-body {
        display: grid;
        grid-template-columns: 25% 75%;
        gap: 10px;
    }

    .resume-design-preview[data-template-category="coluna7525"] .resume-design-preview-body {
        display: grid;
        grid-template-columns: 75% 25%;
        gap: 10px;
    }

    .section-card {
        scroll-margin-top: 16px;
    }
</style>

<section class="card">
    <h1><?= $isEdit ? 'Editar currículo' : 'Criar currículo passo a passo' ?></h1>
    <p class="muted">Preencha com objetividade. Formato recomendado para listas: <strong>campo 1 | campo 2 | campo 3</strong>.</p>
</section>

<section class="card assistant-board">
    <h2>Assistente de preenchimento</h2>
    <p class="muted">Este assistente acompanha a qualidade do currículo em tempo real e ajuda você a preencher os campos mais importantes.</p>

    <div class="assistant-progress-track" aria-hidden="true">
        <div class="assistant-progress-fill" id="assistantProgressFill"></div>
    </div>
    <p class="assistant-progress-text" id="assistantProgressText">Progresso: 0%</p>

    <ul class="assistant-checklist">
        <li data-check="title">Definir título do currículo</li>
        <li data-check="personal_data">Informar dados pessoais de contato</li>
        <li data-check="positioning">Preencher objetivo ou resumo</li>
        <li data-check="experience">Adicionar pelo menos 1 experiência</li>
        <li data-check="education">Adicionar pelo menos 1 formação</li>
        <li data-check="skills">Adicionar habilidades principais</li>
    </ul>

    <div class="assistant-tip" id="assistantTip">
        Dica ativa: comece pelo título e dados pessoais, depois registre experiências com resultados em formato de bullets.
    </div>

    <div class="assistant-anchors">
        <a class="assistant-anchor" href="#section-basico">Dados básicos</a>
        <a class="assistant-anchor" href="#section-posicionamento">Objetivo e resumo</a>
        <a class="assistant-anchor" href="#section-experiencia">Experiência</a>
        <a class="assistant-anchor" href="#section-formacao">Formação</a>
        <a class="assistant-anchor" href="#section-competencias">Competências</a>
        <a class="assistant-anchor" href="#section-complementar">Complementares</a>
    </div>
</section>

<?php if (!empty($error)): ?>
    <div class="flash error"><?= e((string) $error) ?></div>
<?php endif; ?>

<form
    id="resumeForm"
    method="post"
    action="<?= e($isEdit ? base_url('catalog/index.php?route=resume/edit/' . (int) ($resume_id ?? 0)) : base_url('catalog/index.php?route=resume/create')) ?>"
>
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

    <section id="section-basico" class="card section-card">
        <h2>Dados básicos</h2>
        <div class="grid cols-2">
            <div>
                <label for="title">Título do currículo</label>
                <input id="title" name="title" value="<?= e($value('title')) ?>" required data-tip="Use um título claro, por exemplo: Analista Financeiro | Controladoria">
                <p class="field-meta"><span data-counter-for="title">0</span> caracteres</p>
            </div>
            <div>
                <label for="template_id">Template</label>
                <select id="template_id" name="template_id" data-tip="Escolha o template visual. O conteudo continua com estrutura profissional padrao.">
                    <option value="">Selecione</option>
                    <?php foreach (($templates ?? []) as $template): ?>
                        <option value="<?= (int) ($template['template_id'] ?? 0) ?>" data-template-category="<?= e((string) ($template['category'] ?? 'basico')) ?>" <?= ((string) $value('template_id') === (string) ($template['template_id'] ?? '')) ? 'selected' : '' ?>>
                            <?= e((string) ($template['name'] ?? 'Template')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?= $value('status', 'draft') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                    <option value="published" <?= $value('status') === 'published' ? 'selected' : '' ?>>Publicado</option>
                </select>
            </div>
        </div>
    </section>

    <section class="card section-card">
        <h2>Personalização visual</h2>
        <p class="muted">Preset padrão: LinkedIn.</p>
        <div class="grid cols-2">
            <div>
                <label for="font_size">Tamanho da fonte (corpo)</label>
                <select id="font_size" name="font_size">
                    <?php foreach ([10, 11, 12, 13, 14] as $fontSize): ?>
                        <option value="<?= $fontSize ?>" <?= ($value('font_size', '11') === (string) $fontSize) ? 'selected' : '' ?>><?= $fontSize ?> px</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="accent_color">Cor de destaque</label>
                <input id="accent_color" name="accent_color" type="color" value="<?= e($value('accent_color', '#0a66c2')) ?>">
            </div>
            <div>
                <label for="header_bg_color">Cor do fundo do cabeçalho</label>
                <input id="header_bg_color" name="header_bg_color" type="color" value="<?= e($value('header_bg_color', '#f3f8fd')) ?>">
            </div>
            <div>
                <label for="header_text_color">Cor do texto do cabeçalho</label>
                <input id="header_text_color" name="header_text_color" type="color" value="<?= e($value('header_text_color', $value('text_color', '#1f2937'))) ?>">
            </div>
            <div>
                <label for="text_color">Cor do texto do corpo</label>
                <input id="text_color" name="text_color" type="color" value="<?= e($value('text_color', '#1f2937')) ?>">
            </div>
        </div>

        <div class="resume-design-preview" id="resumeDesignPreview" data-template-category="basico" aria-live="polite">
            <div class="resume-design-preview-header">
                <p class="resume-design-preview-name">Nome Sobrenome</p>
                <p class="resume-design-preview-headline">Cargo-alvo e especialidade</p>
                <p class="resume-design-preview-meta">contato@exemplo.com | Cidade/UF</p>
            </div>
            <div class="resume-design-preview-body">
                <p class="resume-design-preview-title">Resumo</p>
                <p class="resume-design-preview-text">Pré-visualização instantânea das cores, tipografia e template.</p>
            </div>
        </div>
    </section>

    <section id="section-posicionamento" class="card section-card">
        <h2>Dados pessoais</h2>
        <p class="muted">Exemplo: Nome completo | email@dominio.com | (11) 99999-9999 | Cidade/UF</p>
        <textarea id="personal_data" name="personal_data" data-tip="Inclua contato profissional direto e localidade."><?= e($value('personal_data')) ?></textarea>
        <p class="field-meta"><span data-counter-for="personal_data">0</span> caracteres</p>
    </section>

    <section class="card section-card">
        <h2>Objetivo profissional</h2>
        <div class="field-actions">
            <button type="button" data-example-target="objective" data-example-value="Atuar como Analista de Dados com foco em automacao de relatorios e melhoria de indicadores de desempenho.">Inserir exemplo</button>
        </div>
        <textarea id="objective" name="objective" data-tip="Explique cargo-alvo e foco de atuacao em ate 2 linhas."><?= e($value('objective')) ?></textarea>
        <p class="field-meta"><span data-counter-for="objective">0</span> caracteres</p>
    </section>

    <section class="card section-card">
        <h2>Resumo profissional</h2>
        <div class="field-actions">
            <button type="button" data-example-target="professional_summary" data-example-value="Profissional com 6 anos em operacoes e analise de indicadores, com foco em produtividade, reducao de custos e melhoria continua.">Inserir exemplo</button>
        </div>
        <textarea id="professional_summary" name="professional_summary" data-tip="Destaque experiência total, especialidades e resultados."><?= e($value('professional_summary')) ?></textarea>
        <p class="field-meta"><span data-counter-for="professional_summary">0</span> caracteres</p>
    </section>

    <section id="section-experiencia" class="card section-card">
        <h2>Experiências</h2>
        <p class="muted">Uma linha por experiencia: <strong>Empresa | Cargo | Inicio (MM/AAAA) | Fim (MM/AAAA ou Atual) | Descricao</strong>. Use <strong>;</strong> para separar bullets de resultado.</p>
        <div class="field-actions">
            <button type="button" data-example-target="experiences_raw" data-example-value="Empresa A | Analista de Operacoes | 01/2022 | Atual | Reduzi custos em 18%; Automatizei relatorios semanais; Treinei 5 analistas juniores">Inserir exemplo</button>
        </div>
        <textarea id="experiences_raw" name="experiences_raw" data-tip="Use datas apenas nos campos de inicio/fim no formato MM/AAAA. Cada ';' vira um bullet no curriculo final."><?= e($value('experiences_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="experiences_raw">0</span> caracteres</p>
    </section>

    <section id="section-formacao" class="card section-card">
        <h2>Formação acadêmica</h2>
        <p class="muted">Uma linha por formacao: <strong>Instituicao | Curso | Inicio (MM/AAAA) | Fim (MM/AAAA ou Atual) | Observacao</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="educations_raw" data-example-value="Universidade Federal de Minas Gerais | Bacharelado em Sistemas de Informacao | 02/2018 | 12/2022 | Enfase em engenharia de software e analise de dados">Inserir exemplo</button>
        </div>
        <textarea id="educations_raw" name="educations_raw" data-tip="Use datas em MM/AAAA e evite datas na descricao."><?= e($value('educations_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="educations_raw">0</span> caracteres</p>
    </section>

    <section id="section-competencias" class="card section-card">
        <h2>Competências técnicas</h2>
        <p class="muted">Uma linha por habilidade: <strong>Habilidade | Nível</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="skills_raw" data-example-value="Python | Avancado">Inserir exemplo</button>
        </div>
        <textarea id="skills_raw" name="skills_raw" data-tip="Liste ferramentas e competências relevantes para a vaga."><?= e($value('skills_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="skills_raw">0</span> caracteres</p>
    </section>

    <section id="section-complementar" class="card section-card">
        <h2>Complementares</h2>

        <h3>Cursos livres</h3>
        <p class="muted">Uma linha por curso: <strong>Curso | Instituicao | Ano</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="courses_raw" data-example-value="Desenvolvimento Web Completo | Udemy | 2025">Inserir exemplo</button>
        </div>
        <textarea id="courses_raw" name="courses_raw" data-tip="Inclua cursos alinhados ao objetivo profissional."><?= e($value('courses_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="courses_raw">0</span> caracteres</p>

        <h3>Idiomas</h3>
        <p class="muted">Uma linha por idioma: <strong>Idioma | Nível</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="languages_raw" data-example-value="Ingles | Avancado">Inserir exemplo</button>
        </div>
        <textarea id="languages_raw" name="languages_raw" data-tip="Exemplo: Ingles | Intermediario."><?= e($value('languages_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="languages_raw">0</span> caracteres</p>

        <h3>Certificações</h3>
        <p class="muted">Uma linha por certificação: <strong>Título | Organização | Ano</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="certifications_raw" data-example-value="AWS Certified Cloud Practitioner | Amazon Web Services | 2025">Inserir exemplo</button>
        </div>
        <textarea id="certifications_raw" name="certifications_raw" data-tip="Priorize certificações atuais e relevantes."><?= e($value('certifications_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="certifications_raw">0</span> caracteres</p>

        <h3>Projetos</h3>
        <p class="muted">Uma linha por projeto: <strong>Nome | Função | Link | Descrição</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="projects_raw" data-example-value="Portal de Curriculos | Desenvolvedor Backend | https://github.com/seunome/portal-curriculos | API e painel para criacao e exportacao de curriculos">Inserir exemplo</button>
        </div>
        <textarea id="projects_raw" name="projects_raw" data-tip="Descreva projeto com impacto e contexto."><?= e($value('projects_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="projects_raw">0</span> caracteres</p>

        <h3>Links profissionais</h3>
        <p class="muted">Uma linha por link: <strong>Rotulo | URL</strong></p>
        <div class="field-actions">
            <button type="button" data-example-target="links_raw" data-example-value="LinkedIn | https://www.linkedin.com/in/seunome">Inserir exemplo</button>
        </div>
        <textarea id="links_raw" name="links_raw" data-tip="Adicione LinkedIn, portfolio, GitHub ou site profissional."><?= e($value('links_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="links_raw">0</span> caracteres</p>
    </section>

    <div class="grid cols-2">
        <button class="button primary" type="submit">Salvar currículo</button>
        <a class="button" href="<?= e(base_url('catalog/index.php?route=dashboard')) ?>">Voltar ao painel</a>
    </div>
</form>

<script>
(() => {
    const form = document.getElementById('resumeForm');
    if (!form) return;

    const progressFill = document.getElementById('assistantProgressFill');
    const progressText = document.getElementById('assistantProgressText');
    const tipBox = document.getElementById('assistantTip');
    const designPreview = document.getElementById('resumeDesignPreview');

    const field = (id) => document.getElementById(id);
    const valueOf = (id) => (field(id)?.value || '').trim();
    const templateSelect = field('template_id');

    const fontMap = {
        basico: '"Calibri", "Segoe UI", sans-serif',
        moderno: '"Trebuchet MS", "Segoe UI", sans-serif',
        profissional: '"Cambria", "Times New Roman", serif',
        criativo: '"Gill Sans", "Trebuchet MS", sans-serif',
        minimalista: '"Arial Narrow", "Segoe UI", sans-serif',
        coluna2575: '"Calibri", "Segoe UI", sans-serif',
        coluna7525: '"Calibri", "Segoe UI", sans-serif'
    };

    const checks = {
        title: () => valueOf('title').length >= 6,
        personal_data: () => valueOf('personal_data').length >= 12,
        positioning: () => valueOf('objective').length >= 20 || valueOf('professional_summary').length >= 30,
        experience: () => valueOf('experiences_raw').length >= 20,
        education: () => valueOf('educations_raw').length >= 10,
        skills: () => valueOf('skills_raw').length >= 8
    };

    const totalChecks = Object.keys(checks).length;

    const updateChecklist = () => {
        let done = 0;
        for (const [key, fn] of Object.entries(checks)) {
            const ok = fn();
            const item = document.querySelector(`[data-check="${key}"]`);
            if (item) item.classList.toggle('done', ok);
            if (ok) done += 1;
        }

        const pct = Math.round((done / totalChecks) * 100);
        if (progressFill) progressFill.style.width = `${pct}%`;
        if (progressText) progressText.textContent = `Progresso: ${pct}% (${done}/${totalChecks} blocos-chave completos)`;
    };

    const updateCounters = () => {
        document.querySelectorAll('[data-counter-for]').forEach((el) => {
            const targetId = el.getAttribute('data-counter-for');
            const target = targetId ? field(targetId) : null;
            el.textContent = String((target?.value || '').length);
        });
    };

    const setTip = (message) => {
        if (!tipBox || !message) return;
        tipBox.textContent = `Dica ativa: ${message}`;
    };

    const updateDesignPreview = () => {
        if (!designPreview) return;

        const accent = valueOf('accent_color') || '#0a66c2';
        const headerBg = valueOf('header_bg_color') || '#f3f8fd';
        const headerText = valueOf('header_text_color') || valueOf('text_color') || '#1f2937';
        const bodyText = valueOf('text_color') || '#1f2937';
        const selectedOption = templateSelect?.options?.[templateSelect.selectedIndex] || null;
        const category = (selectedOption?.getAttribute('data-template-category') || 'basico').toLowerCase();
        const fontSize = Number.parseInt(valueOf('font_size'), 10);
        const safeFontSize = Number.isFinite(fontSize) && fontSize >= 10 && fontSize <= 14 ? fontSize : 11;

        designPreview.dataset.templateCategory = category;
        designPreview.style.setProperty('--preview-accent', accent);
        designPreview.style.setProperty('--preview-header-bg', headerBg);
        designPreview.style.setProperty('--preview-header-text', headerText);
        designPreview.style.setProperty('--preview-body-text', bodyText);
        designPreview.style.setProperty('--preview-font-size', `${safeFontSize}px`);
        designPreview.style.setProperty('--preview-font', fontMap[category] || fontMap.basico);
    };

    form.querySelectorAll('input, textarea, select').forEach((el) => {
        el.addEventListener('input', () => {
            updateChecklist();
            updateCounters();
            updateDesignPreview();
        });
        el.addEventListener('change', () => {
            updateChecklist();
            updateCounters();
            updateDesignPreview();
        });
        el.addEventListener('focus', () => {
            const tip = el.getAttribute('data-tip');
            if (tip) setTip(tip);
        });
    });

    document.querySelectorAll('[data-example-target]').forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-example-target');
            const sample = button.getAttribute('data-example-value') || '';
            const target = targetId ? field(targetId) : null;
            if (!target || sample === '') return;
            if (target.value.trim() === '') {
                target.value = sample;
            } else {
                target.value += `\n${sample}`;
            }
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.focus();
        });
    });

    updateChecklist();
    updateCounters();
    updateDesignPreview();
})();
</script>
