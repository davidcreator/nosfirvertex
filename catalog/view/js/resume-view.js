(() => {
    const resumeRoot = document.querySelector('.resume-linkedin');
    if (resumeRoot) {
        const readHex = (value, fallback) => (/^#[0-9a-fA-F]{6}$/).test(value || '') ? value : fallback;

        const accent = readHex(resumeRoot.dataset.themeAccent, '#0a66c2');
        const headerBg = readHex(resumeRoot.dataset.themeHeaderBg, '#f3f8fd');
        const headerText = readHex(resumeRoot.dataset.themeHeaderText, '#1f2937');
        const textMain = readHex(resumeRoot.dataset.themeText, '#1f2937');

        const fontSizeRaw = Number.parseInt(resumeRoot.dataset.themeFontSize || '11', 10);
        const fontSize = Number.isFinite(fontSizeRaw) && fontSizeRaw >= 10 && fontSizeRaw <= 14 ? fontSizeRaw : 11;

        const fontFamilyRaw = (resumeRoot.dataset.themeFontFamily || '').trim();
        const fontFamily = fontFamilyRaw !== '' ? fontFamilyRaw : '"Calibri", "Segoe UI", sans-serif';

        resumeRoot.style.setProperty('--accent', accent);
        resumeRoot.style.setProperty('--header-bg', headerBg);
        resumeRoot.style.setProperty('--header-text', headerText);
        resumeRoot.style.setProperty('--text-main', textMain);
        resumeRoot.style.setProperty('--base-size', `${fontSize}px`);
        resumeRoot.style.setProperty('--font-body', fontFamily);
    }

    const copyButtons = document.querySelectorAll('[data-copy-text]');
    if (!copyButtons.length) {
        return;
    }

    const copyWithFallback = (text) => {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.setAttribute('readonly', '');
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (error) {
            copied = false;
        }

        document.body.removeChild(textArea);
        return copied;
    };

    copyButtons.forEach((button) => {
        const defaultLabel = button.getAttribute('data-copy-default-label') || (button.textContent || 'Copiar');
        const successLabel = button.getAttribute('data-copy-success-label') || 'Copiado';
        const errorLabel = button.getAttribute('data-copy-error-label') || 'Nao foi possivel copiar';

        button.addEventListener('click', async () => {
            const text = (button.getAttribute('data-copy-text') || '').trim();
            if (!text) {
                return;
            }

            const container = button.closest('.resume-share-tools');
            const status = container ? container.querySelector('[data-copy-status]') : null;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else if (!copyWithFallback(text)) {
                    throw new Error('copy-failed');
                }

                button.textContent = successLabel;
                if (status) {
                    status.textContent = 'Texto pronto para colar na plataforma escolhida.';
                }
            } catch (error) {
                button.textContent = errorLabel;
                if (status) {
                    status.textContent = 'Copie manualmente o PDF ou o link web do curriculo.';
                }
            }

            setTimeout(() => {
                button.textContent = defaultLabel;
            }, 1800);
        });
    });
})();
