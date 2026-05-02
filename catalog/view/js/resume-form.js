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
