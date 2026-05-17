(function () {
    const COOKIE_NAME = 'kaiseki_theme';
    const DEFAULT = 'sombre';

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + expires + '; path=/; SameSite=Lax';
    }

    function getThemePath() {
        const isInPhpFolder = window.location.pathname.includes('/php/');
        return isInPhpFolder ? '../css/theme-clair.css' : 'css/theme-clair.css';
    }

    function applyTheme(theme) {
        if (theme !== 'clair' && theme !== 'sombre') theme = DEFAULT;

        let link = document.getElementById('theme-stylesheet');

        if (theme === 'clair') {
            if (!link) {
                link = document.createElement('link');
                link.rel = 'stylesheet';
                link.id = 'theme-stylesheet';
                document.head.appendChild(link);
            }
            link.href = getThemePath();
        } else {
            if (link) link.remove();
        }

        if (document.body) {
            document.body.setAttribute('data-theme', theme);
        }

        setCookie(COOKIE_NAME, theme, 365);

        const btn = document.getElementById('btn-theme-toggle');
        if (btn) {
            btn.textContent = theme === 'sombre' ? '☀️ MODE CLAIR' : '🌙 MODE SOMBRE';
        }
    }

    function toggleTheme() {
        const current = getCookie(COOKIE_NAME) || DEFAULT;
        applyTheme(current === 'sombre' ? 'clair' : 'sombre');
    }

    // Applique dès que le DOM est prêt
    document.addEventListener('DOMContentLoaded', function () {
        const saved = getCookie(COOKIE_NAME);
        applyTheme(saved || DEFAULT);
    });

    window.toggleTheme = toggleTheme;
})();