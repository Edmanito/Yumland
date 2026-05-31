// =========================================
// KAISEKI SHUNEI — THEME.JS
// Gestion du système de thème clair/sombre.
//
// Fonctionnement :
// - Le thème est stocké dans un cookie (kaiseki_theme)
// - À chaque chargement de page, le cookie est lu et
//   le thème est appliqué automatiquement
// - Si le cookie n'existe pas ou est invalide,
//   le thème par défaut (sombre) est utilisé
// - Si l'utilisateur est connecté, le thème est aussi
//   sauvegardé dans son compte (JSON) via une requête PHP
// =========================================

(function () {

    // Nom du cookie utilisé pour mémoriser le thème
    const COOKIE_NAME = 'kaiseki_theme';

    // Thème appliqué si aucun cookie valide n'est trouvé
    const DEFAULT = 'sombre';

    // -----------------------------------------
    // GESTION DES COOKIES
    // -----------------------------------------

    /**
     * Lit la valeur d'un cookie par son nom.
     * Retourne null si le cookie n'existe pas.
     */
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    /**
     * Crée ou met à jour un cookie.
     * - path=/ : accessible sur toutes les pages du site
     * - SameSite=Lax : protection contre les attaques CSRF
     * - expires : durée de vie en jours (365 = 1 an)
     */
    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value)
            + '; expires=' + expires
            + '; path=/; SameSite=Lax';
    }

    // -----------------------------------------
    // CHEMIN VERS LE CSS DU THÈME CLAIR
    // -----------------------------------------

    /**
     * Détecte si la page courante est dans le dossier /php/
     * et retourne le bon chemin relatif vers theme-clair.css.
     * - Pages à la racine (index.php) : css/theme-clair.css
     * - Pages dans /php/ (carte, profil...) : ../css/theme-clair.css
     */
    function getThemePath() {
        const isInPhpFolder = window.location.pathname.includes('/php/');
        return isInPhpFolder ? '../css/theme-clair.css' : 'css/theme-clair.css';
    }

    // -----------------------------------------
    // APPLICATION DU THÈME
    // -----------------------------------------

    /**
     * Applique le thème demandé sur la page courante.
     * - Mode clair : injecte dynamiquement theme-clair.css dans le <head>
     * - Mode sombre : supprime le lien vers theme-clair.css (CSS par défaut)
     * - Met à jour l'attribut data-theme sur le <body>
     * - Sauvegarde le choix dans le cookie (365 jours)
     * - Met à jour le texte du bouton dans le menu
     */
    function applyTheme(theme) {
        // Sécurité : si la valeur est inconnue, on remet le thème par défaut
        if (theme !== 'clair' && theme !== 'sombre') theme = DEFAULT;

        // Récupère le lien CSS du thème clair s'il existe déjà
        let link = document.getElementById('theme-stylesheet');

        if (theme === 'clair') {
            // Crée le lien CSS si pas encore présent dans le <head>
            if (!link) {
                link = document.createElement('link');
                link.rel = 'stylesheet';
                link.id = 'theme-stylesheet';
                document.head.appendChild(link);
            }
            // Pointe vers le bon fichier CSS selon la page
            link.href = getThemePath();
        } else {
            // Mode sombre : on retire le CSS clair pour revenir au style par défaut
            if (link) link.remove();
        }

        // Ajoute data-theme="clair" ou data-theme="sombre" sur le body
        // (peut servir pour des règles CSS ciblées)
        if (document.body) {
            document.body.setAttribute('data-theme', theme);
        }

        // Mémorise le choix dans le cookie pendant 365 jours
        setCookie(COOKIE_NAME, theme, 365);

        // Met à jour le label du bouton dans le menu latéral
        const btn = document.getElementById('btn-theme-toggle');
        if (btn) {
            btn.textContent = theme === 'sombre' ? '☀️ MODE CLAIR' : '🌙 MODE SOMBRE';
        }
    }

    // -----------------------------------------
    // BASCULEMENT DU THÈME (bouton)
    // -----------------------------------------

    /**
     * Appelée quand l'utilisateur clique sur le bouton de thème.
     * Lit le thème actuel dans le cookie, bascule vers l'autre,
     * applique le changement et le sauvegarde côté serveur
     * si l'utilisateur est connecté (via sauvegarder_theme.php).
     */
    function toggleTheme() {
        const current = getCookie(COOKIE_NAME) || DEFAULT;
        const newTheme = current === 'sombre' ? 'clair' : 'sombre';

        // Applique immédiatement le nouveau thème
        applyTheme(newTheme);

        // Sauvegarde dans le compte utilisateur si connecté
        // Cela permet de restaurer le bon thème à la prochaine connexion
        const isInPhpFolder = window.location.pathname.includes('/php/');
        const actionPath = isInPhpFolder
            ? '../actions/sauvegarder_theme.php'
            : 'actions/sauvegarder_theme.php';

        fetch(actionPath, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'theme=' + newTheme
        });
        // Note : si l'utilisateur n'est pas connecté, le serveur ignore
        // la requête et seul le cookie est conservé
    }

    // -----------------------------------------
    // INITIALISATION AU CHARGEMENT DE LA PAGE
    // -----------------------------------------

    // Dès que le DOM est prêt, on relit le cookie et on applique le thème.
    // Le script anti-flash dans le <head> de chaque page PHP gère déjà
    // l'injection du CSS avant le rendu, mais cette partie met à jour
    // le bouton et l'attribut data-theme une fois le body disponible.
    document.addEventListener('DOMContentLoaded', function () {
        const saved = getCookie(COOKIE_NAME);
        applyTheme(saved || DEFAULT);
    });

    // Expose toggleTheme() globalement pour que le bouton HTML
    // puisse l'appeler via onclick="toggleTheme()"
    window.toggleTheme = toggleTheme;

})();
// La fonction s'auto-exécute (IIFE) pour éviter de polluer
// le scope global avec les variables internes (COOKIE_NAME, DEFAULT...)