(() => {
    const STABLE_KEY = 'squadro:stable-board-viewport';

    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    document.documentElement.classList.add('squadro-no-smooth-scroll');

    const qs = (selector, root = document) => root.querySelector(selector);
    const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

    const clearPreviews = () => {
        qsa('.preview-destination').forEach((cell) => cell.classList.remove('preview-destination'));
    };

    const boardStage = () => qs('.board-stage');

    const rememberBoardViewport = () => {
        const stage = boardStage();
        if (!stage) return;

        const rect = stage.getBoundingClientRect();
        const visible = rect.bottom > 0 && rect.top < window.innerHeight;
        if (!visible) return;

        sessionStorage.setItem(STABLE_KEY, JSON.stringify({
            top: rect.top,
            left: rect.left,
            scrollY: window.scrollY,
            time: Date.now(),
        }));
    };

    const stabilizeBoardViewport = () => {
        const raw = sessionStorage.getItem(STABLE_KEY);
        if (!raw) return;

        let previous;
        try {
            previous = JSON.parse(raw);
        } catch {
            sessionStorage.removeItem(STABLE_KEY);
            return;
        }

        if (!previous || typeof previous.top !== 'number' || Date.now() - Number(previous.time || 0) > 12000) {
            sessionStorage.removeItem(STABLE_KEY);
            return;
        }

        const restore = () => {
            const stage = boardStage();
            if (!stage) {
                sessionStorage.removeItem(STABLE_KEY);
                return;
            }

            stage.classList.add('is-transition-locked');
            const currentTop = stage.getBoundingClientRect().top;
            const delta = currentTop - previous.top;

            if (Math.abs(delta) > 1) {
                window.scrollBy({ top: delta, left: 0, behavior: 'auto' });
            }

            window.setTimeout(() => {
                stage.classList.remove('is-transition-locked');
            }, 90);

            sessionStorage.removeItem(STABLE_KEY);
        };

        requestAnimationFrame(() => requestAnimationFrame(restore));
    };

    const previewDestination = (button) => {
        clearPreviews();
        if (!button.dataset.destination) return;
        const target = qs(`[data-cell="${button.dataset.destination}"]`);
        if (target) target.classList.add('preview-destination');
    };

    const submitterAllowsAjax = (submitter) => {
        if (!submitter) return true;
        const name = submitter.getAttribute('name') || '';
        return !['export', 'menu'].includes(name);
    };

    const isGameCommandForm = (form, submitter) => {
        if (!qs('.game-shell')) return false;
        if (!form.matches('form')) return false;
        if (!submitterAllowsAjax(submitter)) return false;

        const action = form.getAttribute('action') || '';
        return action.includes('traiteActionSquadro.php');
    };

    const nativeSubmit = (form, submitter) => {
        if (submitter && submitter.name) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = submitter.name;
            input.value = submitter.value;
            form.appendChild(input);
        }
        HTMLFormElement.prototype.submit.call(form);
    };

    const refreshGameShell = (html) => {
        const parser = new DOMParser();
        const nextDoc = parser.parseFromString(html, 'text/html');
        const nextShell = qs('.game-shell', nextDoc);
        const currentShell = qs('.game-shell');

        if (!nextShell || !currentShell) {
            window.location.reload();
            return;
        }

        rememberBoardViewport();
        document.title = nextDoc.title || document.title;
        document.body.className = nextDoc.body.className;
        currentShell.replaceWith(nextShell);
        clearPreviews();
        initialize(document);
        stabilizeBoardViewport();
    };

    const ajaxSubmit = async (form, submitter) => {
        if (form.dataset.ajaxBusy === '1') return;

        rememberBoardViewport();
        form.dataset.ajaxBusy = '1';
        form.classList.add('is-loading');
        if (submitter) submitter.classList.add('is-submitting');

        try {
            const formData = submitter ? new FormData(form, submitter) : new FormData(form);
            const response = await fetch(form.action || window.location.href, {
                method: (form.method || 'POST').toUpperCase(),
                body: formData,
                credentials: 'same-origin',
                redirect: 'follow',
                headers: { 'X-Requested-With': 'SquadroAjax' },
            });

            const contentType = response.headers.get('content-type') || '';
            if (!response.ok || !contentType.includes('text/html')) {
                nativeSubmit(form, submitter);
                return;
            }

            refreshGameShell(await response.text());
        } catch {
            nativeSubmit(form, submitter);
        } finally {
            form.dataset.ajaxBusy = '0';
            form.classList.remove('is-loading');
            if (submitter) submitter.classList.remove('is-submitting');
        }
    };

    const clickButton = (selector) => {
        const button = qs(selector);
        if (button && !button.disabled) button.click();
    };

    const enabledPieces = () => qsa('.piece-button:not(:disabled)');

    const toggleFullscreenBoard = async () => {
        const stage = boardStage();
        if (!stage) return;

        rememberBoardViewport();
        try {
            if (!document.fullscreenElement) {
                await stage.requestFullscreen();
                stage.classList.add('is-fullscreen');
            } else {
                await document.exitFullscreen();
                stage.classList.remove('is-fullscreen');
            }
        } catch {
            stage.classList.toggle('is-focus-mode');
            stabilizeBoardViewport();
        }
    };

    const initialize = (root = document) => {
        qsa('.piece-button[data-destination]', root).forEach((button) => {
            if (button.dataset.boundPreview === '1') return;
            button.dataset.boundPreview = '1';

            button.addEventListener('mouseenter', () => previewDestination(button));
            button.addEventListener('mouseleave', clearPreviews);
            button.addEventListener('focus', () => previewDestination(button));
            button.addEventListener('blur', clearPreviews);
            button.addEventListener('click', () => {
                rememberBoardViewport();
                button.classList.add('is-submitting');
                sessionStorage.setItem('squadro:lastAction', button.dataset.origin || 'piece');
            });
        });

        qsa('form', root).forEach((form) => {
            if (form.dataset.boundSubmit === '1') return;
            form.dataset.boundSubmit = '1';

            form.addEventListener('submit', (event) => {
                const submitter = event.submitter || document.activeElement;
                if (isGameCommandForm(form, submitter)) {
                    event.preventDefault();
                    ajaxSubmit(form, submitter instanceof HTMLElement ? submitter : null);
                    return;
                }

                rememberBoardViewport();
                form.classList.add('is-loading');
            });
        });

        const board = qs('.squadro-board', root);
        if (board && board.dataset.boundLeave !== '1') {
            board.dataset.boundLeave = '1';
            board.addEventListener('mouseleave', clearPreviews);
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
                rememberBoardViewport();
                piece.click();
            }
            return;
        }

        if (key === 'o') {
            event.preventDefault();
            rememberBoardViewport();
            clickButton('button[name="oracle"]');
        }

        if (key === 'u') {
            event.preventDefault();
            rememberBoardViewport();
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
            if (abort) {
                event.preventDefault();
                rememberBoardViewport();
                abort.click();
            }
        }

        if (key === 'enter') {
            const confirm = qs('button[name="choix"][value="PRESEED"]');
            if (confirm) {
                event.preventDefault();
                rememberBoardViewport();
                confirm.click();
            }
        }
    });

    window.addEventListener('beforeunload', rememberBoardViewport);

    initialize(document);
    stabilizeBoardViewport();
})();
