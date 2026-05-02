(() => {
    const buttons = document.querySelectorAll('[data-copy-target]');
    if (!buttons.length) {
        return;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const targetId = button.getAttribute('data-copy-target');
            const target = targetId ? document.getElementById(targetId) : null;
            if (!target) {
                return;
            }

            const text = (target.textContent || '').trim();
            if (!text) {
                return;
            }

            const successLabel = button.getAttribute('data-copy-success-label') || 'Chave copiada';
            const errorLabel = button.getAttribute('data-copy-error-label') || 'Nao foi possivel copiar';

            try {
                await navigator.clipboard.writeText(text);
                const original = button.textContent;
                button.textContent = successLabel;
                setTimeout(() => {
                    button.textContent = original;
                }, 1800);
            } catch (error) {
                button.textContent = errorLabel;
            }
        });
    });
})();
