(() => {
    const root = document.body;
    if (!root || (!root.classList.contains('greek-theme') && !root.classList.contains('sparta-home'))) {
        return;
    }

    requestAnimationFrame(() => root.classList.add('greek-loaded'));

    const buttons = document.querySelectorAll('.btn, .menu-action, .sparta-button, .piece-button:not(:disabled)');
    buttons.forEach((button) => {
        button.addEventListener('pointerenter', () => button.classList.add('is-lit'));
        button.addEventListener('pointerleave', () => button.classList.remove('is-lit'));
    });
})();
