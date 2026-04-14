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
                        <option value="<?= (int) ($template['template_id'] ?? 0) ?>" <?= ((string) $value('template_id') === (string) ($template['template_id'] ?? '')) ? 'selected' : '' ?>>
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
        <p class="muted">Preset padrao: LinkedIn.</p>
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
                <label for="text_color">Cor do texto do corpo</label>
                <input id="text_color" name="text_color" type="color" value="<?= e($value('text_color', '#1f2937')) ?>">
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
        <p class="muted">Uma linha por experiência: <strong>Empresa | Cargo | Início | Fim | Descrição</strong>. Use <strong>;</strong> para separar bullets de resultado.</p>
        <div class="field-actions">
            <button type="button" data-example-target="experiences_raw" data-example-value="Empresa A | Analista de Operações | 2022 | Atual | Reduzi custos em 18%; Automatizei relatórios semanais; Treinei 5 analistas juniores">Inserir exemplo</button>
        </div>
        <textarea id="experiences_raw" name="experiences_raw" data-tip="Foque em resultados mensuráveis. Cada ';' vira um bullet no currículo final."><?= e($value('experiences_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="experiences_raw">0</span> caracteres</p>
    </section>

    <section id="section-formacao" class="card section-card">
        <h2>Formação acadêmica</h2>
        <p class="muted">Uma linha por formação: <strong>Instituição | Curso | Início | Fim | Observação</strong></p>
        <textarea id="educations_raw" name="educations_raw" data-tip="Adicione formação mais recente primeiro."><?= e($value('educations_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="educations_raw">0</span> caracteres</p>
    </section>

    <section id="section-competencias" class="card section-card">
        <h2>Competências técnicas</h2>
        <p class="muted">Uma linha por habilidade: <strong>Habilidade | Nível</strong></p>
        <textarea id="skills_raw" name="skills_raw" data-tip="Liste ferramentas e competências relevantes para a vaga."><?= e($value('skills_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="skills_raw">0</span> caracteres</p>
    </section>

    <section id="section-complementar" class="card section-card">
        <h2>Complementares</h2>

        <h3>Cursos</h3>
        <p class="muted">Uma linha por curso: <strong>Curso | Instituicao | Ano</strong></p>
        <textarea id="courses_raw" name="courses_raw" data-tip="Inclua cursos alinhados ao objetivo profissional."><?= e($value('courses_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="courses_raw">0</span> caracteres</p>

        <h3>Idiomas</h3>
        <p class="muted">Uma linha por idioma: <strong>Idioma | Nível</strong></p>
        <textarea id="languages_raw" name="languages_raw" data-tip="Exemplo: Ingles | Intermediario."><?= e($value('languages_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="languages_raw">0</span> caracteres</p>

        <h3>Certificações</h3>
        <p class="muted">Uma linha por certificação: <strong>Título | Organização | Ano</strong></p>
        <textarea id="certifications_raw" name="certifications_raw" data-tip="Priorize certificações atuais e relevantes."><?= e($value('certifications_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="certifications_raw">0</span> caracteres</p>

        <h3>Projetos</h3>
        <p class="muted">Uma linha por projeto: <strong>Nome | Função | Link | Descrição</strong></p>
        <textarea id="projects_raw" name="projects_raw" data-tip="Descreva projeto com impacto e contexto."><?= e($value('projects_raw')) ?></textarea>
        <p class="field-meta"><span data-counter-for="projects_raw">0</span> caracteres</p>

        <h3>Links profissionais</h3>
        <p class="muted">Uma linha por link: <strong>Rotulo | URL</strong></p>
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

    const field = (id) => document.getElementById(id);
    const valueOf = (id) => (field(id)?.value || '').trim();

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

    form.querySelectorAll('input, textarea, select').forEach((el) => {
        el.addEventListener('input', () => {
            updateChecklist();
            updateCounters();
        });
        el.addEventListener('change', () => {
            updateChecklist();
            updateCounters();
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
})();
</script>
