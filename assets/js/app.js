(() => {
    const qs = (selector, root = document) => root.querySelector(selector);
    const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

    const clearPreviews = () => {
        qsa('.preview-destination').forEach((cell) => cell.classList.remove('preview-destination'));
    };

    const previewDestination = (button) => {
        clearPreviews();
        if (!button.dataset.destination) return;
        const target = qs(`[data-cell="${button.dataset.destination}"]`);
        if (target) target.classList.add('preview-destination');
    };

    qsa('.piece-button[data-destination]').forEach((button) => {
        button.addEventListener('mouseenter', () => previewDestination(button));
        button.addEventListener('mouseleave', clearPreviews);
        button.addEventListener('focus', () => previewDestination(button));
        button.addEventListener('blur', clearPreviews);
        button.addEventListener('click', () => {
            button.classList.add('is-submitting');
            sessionStorage.setItem('squadro:lastAction', button.dataset.origin || 'piece');
        });
    });

    qsa('form').forEach((form) => {
        form.addEventListener('submit', () => {
            form.classList.add('is-loading');
        });
    });

    const clickButton = (selector) => {
        const button = qs(selector);
        if (button && !button.disabled) button.click();
    };

    const enabledPieces = () => qsa('.piece-button:not(:disabled)');

    const toggleFullscreenBoard = async () => {
        const boardStage = qs('.board-stage');
        if (!boardStage) return;

        try {
            if (!document.fullscreenElement) {
                await boardStage.requestFullscreen();
                boardStage.classList.add('is-fullscreen');
            } else {
                await document.exitFullscreen();
                boardStage.classList.remove('is-fullscreen');
            }
        } catch {
            boardStage.classList.toggle('is-focus-mode');
            boardStage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    document.addEventListener('fullscreenchange', () => {
        qsa('.board-stage').forEach((stage) => {
            stage.classList.toggle('is-fullscreen', document.fullscreenElement === stage);
        });
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const typing = target instanceof HTMLInputElement || target instanceof HTMLSelectElement || target instanceof HTMLTextAreaElement;
        if (typing) return;

        const key = event.key.toLowerCase();
        if (event.ctrlKey || event.metaKey || event.altKey) return;

        if (/^[1-5]$/.test(key)) {
            const index = Number(key) - 1;
            const piece = enabledPieces()[index];
            if (piece) {
                event.preventDefault();
                piece.click();
            }
            return;
        }

        if (key === 'o') {
            event.preventDefault();
            clickButton('button[name="oracle"]');
        }

        if (key === 'u') {
            event.preventDefault();
            clickButton('button[name="undo"]');
        }

        if (key === 'm') {
            event.preventDefault();
            clickButton('button[name="menu"]');
        }

        if (key === 'f') {
            event.preventDefault();
            toggleFullscreenBoard();
        }

        if (key === 'escape') {
            const abort = qs('button[name="choix"][value="ABORT"]');
            if (abort) abort.click();
        }

        if (key === 'enter') {
            const confirm = qs('button[name="choix"][value="PRESEED"]');
            if (confirm) confirm.click();
        }
    });

    const board = qs('.squadro-board');
    if (board) {
        board.addEventListener('mouseleave', clearPreviews);
    }
})();
