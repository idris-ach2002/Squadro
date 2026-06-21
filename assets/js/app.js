(() => {
    const clearPreviews = () => {
        document.querySelectorAll('.preview-destination').forEach((cell) => {
            cell.classList.remove('preview-destination');
        });
    };

    document.querySelectorAll('.piece-button[data-destination]').forEach((button) => {
        button.addEventListener('mouseenter', () => {
            clearPreviews();
            const target = document.querySelector(`[data-cell="${button.dataset.destination}"]`);
            if (target) target.classList.add('preview-destination');
        });
        button.addEventListener('mouseleave', clearPreviews);
        button.addEventListener('focus', () => {
            clearPreviews();
            const target = document.querySelector(`[data-cell="${button.dataset.destination}"]`);
            if (target) target.classList.add('preview-destination');
        });
        button.addEventListener('blur', clearPreviews);
        button.addEventListener('click', () => {
            button.classList.add('is-submitting');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key.toLowerCase() === 'r' && event.ctrlKey) return;
        if (event.key.toLowerCase() === 'm') {
            const menu = document.querySelector('button[name="menu"]');
            if (menu) menu.click();
        }
        if (event.key.toLowerCase() === 'u') {
            const undo = document.querySelector('button[name="undo"]');
            if (undo) undo.click();
        }
    });
})();
