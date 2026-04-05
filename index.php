<?php
require_once 'includes/config.php';
require_once 'includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erreurs = [
    'champs_vides'            => 'Veuillez remplir tous les champs.',
    'identifiants_incorrects' => 'Email ou mot de passe incorrect.',
    'compte_suspendu'         => 'Votre compte a été suspendu. Contactez l\'administration.'
];
$erreur = isset($_GET['erreur']) ? ($erreurs[$_GET['erreur']] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaiseki Shunei | Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@200;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/commun.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .auth-error { background: rgba(255,70,70,0.1); border: 1px solid rgba(255,70,70,0.3); color: #ff6b6b; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; }
        .auth-subtitle { color: #888; font-size: 0.85rem; margin-bottom: 20px; display: block; }
        .switch-auth { margin-top: 15px; font-size: 0.8rem; color: #666; }
    </style>
</head>
<body class="page-accueil">

    <!-- ── MENU LATÉRAL BLEU ── -->
    <div id="side-menu" class="side-panel">
        <div class="menu-content-wrapper">
            <div class="menu-links">
                <?php if (estConnecte()): ?>
                    <a href="php/carte.php">RÉSERVER</a>
                <?php else: ?>
                    <a href="javascript:void(0)" onclick="openReservationFromMenu()">RÉSERVER</a>
                <?php endif; ?>
                <a href="#restaurant" onclick="toggleMenu()">LE RESTAURANT</a>
                <a href="#chefs" onclick="toggleMenu()">LES CHEFS</a>
                <a href="#experience" onclick="toggleMenu()">L'EXPÉRIENCE</a>
                <a href="php/carte.php">LE MENU</a>
                <a href="#informations" onclick="toggleMenu()">INFORMATIONS</a>
            </div>
        </div>

        <div class="menu-footer">
            <div class="menu-footer-separator"></div>
            <a href="javascript:void(0)" class="admin-link" onclick="accesSecurise(); toggleMenu();">ADMINISTRATION</a>
            <div class="menu-footer-line"></div>

            <!-- RÉSEAUX SOCIAUX -->
            <div class="social-links">
                <a href="https://www.instagram.com/kaisekishunei_off" target="_blank" title="Instagram">
                    <img src="img/instagram-icon.png" alt="Instagram">
                </a>
                <a href="https://www.tiktok.com/@kaisekishunei_off" target="_blank" title="TikTok">
                    <img src="img/tiktok-icon.png" alt="TikTok">
                </a>
                <a href="https://www.youtube.com/@kaisekishunei_off" target="_blank" title="YouTube">
                    <img src="img/youtube-icon.png" alt="YouTube">
                </a>
                <a href="https://www.twitter.com/kaisekishunei_off" target="_blank" title="Twitter / X">
                    <img src="img/tweeter-icon.png" alt="Twitter">
                </a>
            </div>

            <!-- BOUTON LANGUE — 15 langues -->
            <div class="lang-wrapper">
                <button class="lang-btn" onclick="toggleLang(event)">
                    <span style="font-size:1.1rem;">🌐</span>
                    <span id="lang-current">FR</span>
                </button>
                <div class="lang-dropdown" id="lang-dropdown">
                    <a href="#" onclick="setLang('FR', event)">🇫🇷 Français</a>
                    <a href="#" onclick="setLang('EN', event)">🇬🇧 English</a>
                    <a href="#" onclick="setLang('ES', event)">🇪🇸 Español</a>
                    <a href="#" onclick="setLang('DE', event)">🇩🇪 Deutsch</a>
                    <a href="#" onclick="setLang('JA', event)">🇯🇵 日本語</a>
                    <a href="#" onclick="setLang('RU', event)">🇷🇺 Русский</a>
                    <a href="#" onclick="setLang('AR', event)">🇸🇦 العربية</a>
                    <a href="#" onclick="setLang('KO', event)">🇰🇷 한국어</a>
                    <a href="#" onclick="setLang('ZH', event)">🇨🇳 中文</a>
                    <a href="#" onclick="setLang('IT', event)">🇮🇹 Italiano</a>
                    <a href="#" onclick="setLang('PT', event)">🇵🇹 Português</a>
                    <a href="#" onclick="setLang('NL', event)">🇳🇱 Nederlands</a>
                    <a href="#" onclick="setLang('HI', event)">🇮🇳 हिन्दी</a>
                    <a href="#" onclick="setLang('TR', event)">🇹🇷 Türkçe</a>
                    <a href="#" onclick="setLang('PL', event)">🇵🇱 Polski</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── HEADER ── -->
    <header class="main-header">
        <div class="header-left">
            <div class="logo-and-menu">
                <div class="logo-kanji"><span>春</span><span>栄</span><span>製</span></div>
                <div class="menu-trigger" onclick="toggleMenu()">
                    <div class="hamburger-icon"><span></span><span></span><span></span></div>
                    <span class="menu-text">MENU</span>
                </div>
            </div>
            <div class="nav-branding">
                <h1 class="brand-name">
                <?php if (estConnecte()): ?>
                    <div style="display:flex;flex-direction:column;justify-content:center;margin-left:5px;">
                        <span style="font-family:'Montserrat';font-size:0.6rem;letter-spacing:5px;opacity:0.6;margin-bottom:2px;">BIENVENUE</span>
                        <span style="color:var(--gold);font-family:'Playfair Display';font-size:1.4rem;font-weight:700;letter-spacing:3px;padding-left:20px;font-style:italic;">
                            <?= strtoupper(htmlspecialchars($_SESSION['user']['infos']['prenom'])) ?>
                        </span>
                    </div>
                <?php else: ?>
                    KAISEKI SHUNEI
                <?php endif; ?>
                </h1>
            </div>
        </div>

        <div class="header-right">
            <?php if (estConnecte()): ?>
                <a href="actions/logout.php" class="btn-deconnexion">DÉCONNEXION</a>
                <div class="profile-trigger" onclick="window.location.href='php/profil.php'">
                    <img src="img/profil-vide.png" alt="Profil" class="profile-icon-nav">
                </div>
                <a href="php/carte.php" class="btn-reservation">COMMANDER</a>
            <?php else: ?>
                <div class="profile-trigger" onclick="toggleReservation()">
                    <img src="img/profil-vide.png" alt="Profil" class="profile-icon-nav">
                </div>
                <a href="javascript:void(0)" class="btn-reservation" onclick="toggleReservation()">CONNEXION</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- ── HERO ── -->
    <section class="hero-section">
        <div class="hero-bg-image"></div>
        <div class="hero-content">
            <h2 class="fade-in">L'Art de la Perfection</h2>
            <div class="search-box">
                <input type="text" placeholder="Rechercher une saveur..." class="input-search">
            </div>
        </div>
    </section>

    <!-- ── PANEL CONNEXION POURPRE ── -->
    <div id="reservation-panel" class="side-panel-right">
        <div class="close-reservation" onclick="toggleReservation()">✕</div>
        <div class="auth-container">
            <div class="auth-box">
                <h3>CONNEXION</h3>
                <span class="auth-subtitle">Accédez à votre espace Kaiseki</span>

                <?php if ($erreur): ?>
                    <div class="auth-error"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <form action="php/connexion.php" method="POST">
                    <input type="email" name="email" placeholder="Email" class="input-auth" required>
                    <input type="password" name="password" placeholder="Mot de passe" class="input-auth" required>
                    <button type="submit" class="btn-submit">SE CONNECTER</button>
                </form>
                <p class="switch-auth">
                    Pas encore de compte ?
                    <a href="php/inscription.php" style="color:var(--gold);text-decoration:none;">S'inscrire</a>
                </p>
            </div>
        </div>
    </div>

    <!-- ── RESTAURANT ── -->
    <section id="restaurant" class="scroll-section restaurant-view">
        <div class="restaurant-bg"></div>
        <div class="blue-overlay"></div>
        <div class="restaurant-content-wrapper">
            <div class="close-gallery-btn" onclick="closeGallery()">✕</div>
            <h2 class="center-title" onclick="openGallery()" style="cursor:pointer">LE RESTAURANT</h2>
            <div class="arrow-container left" onclick="changeImage(-1)">
                <img src="img/retour.png" class="arrow-icon" style="cursor:pointer">
            </div>
            <div id="viewer-container" class="viewer-container">
                <img src="img/resto-1.png" id="main-photo" alt="Galerie Shunei">
            </div>
            <div class="arrow-container right" onclick="changeImage(1)">
                <img src="img/retour.png" class="arrow-icon mirror" style="cursor:pointer">
            </div>
            <div class="story-bottom-right">
                <div class="story-box">
                    <span class="section-subtitle">NOTRE HÉRITAGE</span>
                    <p>"Sous l'ombre des cerisiers de Kyoto..."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── CHEFS ── -->
    <section id="chefs" class="scroll-section chefs-view">
        <div class="chefs-container">
            <div class="chef-card" onclick="ouvrirHistoire('kenji')">
                <div class="chef-img-wrapper">
                    <img src="img/chef-1.jpeg" class="chef-portrait" alt="Maître Kenji">
                </div>
                <div class="chef-info">
                    <span class="chef-role">MAÎTRE DU FEU</span>
                    <h3>KENJI</h3>
                </div>
            </div>
            <div class="chefs-divider">
                <div class="line"></div>
                <div class="kanji-split">絆</div>
                <div class="line"></div>
            </div>
            <div class="chef-card" onclick="ouvrirHistoire('aiko')">
                <div class="chef-img-wrapper">
                    <img src="img/chef-2.jpeg" class="chef-portrait" alt="Chef Aiko">
                </div>
                <div class="chef-info">
                    <span class="chef-role">ÂME CRÉATRICE</span>
                    <h3>AIKO</h3>
                </div>
            </div>
        </div>
        <div id="chef-overlay" class="chef-story-overlay">
            <div class="story-paper">
                <div class="close-story" onclick="fermerHistoire()">✕</div>
                <div class="ink-stamp">春栄</div>
                <div id="story-content"></div>
            </div>
        </div>
    </section>

    <!-- ── EXPÉRIENCE ── -->
    <section id="experience" class="scroll-section experience-view">
        <div class="experience-content">
            <span class="section-subtitle">NOTRE ODYSSÉE</span>
            <h2 class="section-title-gold">L'Équilibre Absolu</h2>
            <div class="timeline-container">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">2012</div>
                    <div class="timeline-content">
                        <span class="kanji-bg">出会い</span>
                        <h3>La Rencontre</h3>
                        <p>Kenji et Aiko croisent leurs chemins dans les cuisines d'un grand Ryokan à Kyoto.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">2018</div>
                    <div class="timeline-content">
                        <span class="kanji-bg">旅立ち</span>
                        <h3>L'Ancrage Parisien</h3>
                        <p>Ouverture de Shunei au cœur de Montmartre.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">2024</div>
                    <div class="timeline-content">
                        <span class="kanji-bg">栄光</span>
                        <h3>La Consécration</h3>
                        <p>Le restaurant devient une référence mondiale du Kaiseki moderne.</p>
                    </div>
                </div>
            </div>
            <div class="exp-quote-wrapper">
                <div class="quote-line"></div>
                <p class="exp-quote-large">"Le sushi est un pont entre nos deux mondes."</p>
                <div class="quote-line"></div>
            </div>
        </div>
    </section>

    <!-- ── MENU ── -->
    <section id="menu" class="scroll-section menu-view-minimal">
        <div class="menu-bg-overlay"></div>
        <a href="php/carte.php" class="menu-compact-box">
            <span class="section-subtitle">DÉCOUVRIR</span>
            <h2 class="menu-title-small">LA CARTE</h2>
            <div class="line-gold"></div>
            <p class="click-info">ENTRER DANS L'EXPÉRIENCE</p>
        </a>
    </section>

    <!-- ── INFORMATIONS ── -->
    <section id="informations" class="scroll-section info-view">
        <div class="info-wrapper">
            <div class="info-block">
                <span class="section-subtitle">NOUS TROUVER</span>
                <h2>CONTACT</h2>
                <div class="contact-details">
                    <div class="detail-item"><p class="label">ADRESSE</p><p>3 Rue André del Sarte, 75018 Paris</p></div>
                    <div class="detail-item"><p class="label">HORAIRES</p><p>Mardi — Samedi : 19:00 - 22:30</p></div>
                    <div class="detail-item"><p class="label">RÉSERVATIONS</p><p>01 42 55 71 11</p></div>
                </div>
            </div>
            <footer class="site-footer">
                <p>© 2026 KAISEKI SHUNEI — TOUS DROITS RÉSERVÉS</p>
                <div class="footer-bottom-line"></div>
            </footer>
        </div>
    </section>

    <!-- ── SCRIPTS ── -->
    <script src="js/index.js"></script>
    <script src="js/langue.js"></script>
    <script>
        // ── MENU ──
        function toggleMenu() {
            document.getElementById("side-menu").classList.toggle("open");
            document.body.classList.toggle("open-nav");
        }

        function toggleReservation() {
            document.getElementById("reservation-panel").classList.toggle("open");
        }

        function openReservationFromMenu() {
            toggleMenu();
            setTimeout(toggleReservation, 500);
        }

        // ── PROFIL ──
        function gererClicProfil() {
            <?php if (estConnecte()): ?>
                window.location.href = 'php/profil.php';
            <?php else: ?>
                toggleReservation();
            <?php endif; ?>
        }

        // ── ACCÈS SÉCURISÉ ──
        function accesSecurise() {
            const code = prompt("Veuillez entrer votre code d'accès :");
            if (code === null) return;
            const choix = code.trim().toLowerCase();
            if (choix === "administration")  { window.location.href = "php/admin.php"; }
            else if (choix === "commande")   { window.location.href = "php/commande.php"; }
            else if (choix === "livraison")  { window.location.href = "php/livraison.php"; }
            else { alert("ACCÈS REFUSÉ !"); }
        }

        // ── GALERIE ──
        let currentImg = 1;
        function changeImage(direction) {
            const photo = document.getElementById("main-photo");
            currentImg += direction;
            if (currentImg > 6) currentImg = 1;
            if (currentImg < 1) currentImg = 6;
            photo.src = `img/resto-${currentImg}.png`;
        }

        function openGallery() {
            document.getElementById("restaurant").classList.add("gallery-active");
            document.body.classList.add("no-scroll");
        }

        function closeGallery() {
            document.getElementById("restaurant").classList.remove("gallery-active");
            document.body.classList.remove("no-scroll");
        }

        // ── CHEFS ──
        const histoires = {
            kenji: { titre: "Maître Kenji", texte: "Né sous les neiges éternelles d'Hokkaido, Kenji a appris très tôt que la cuisine est une discipline de l'esprit avant d'être celle des mains. Son parcours l'a mené des ports de pêche glacés du Nord aux cuisines impériales de Tokyo." },
            aiko:  { titre: "Chef Aiko",    texte: "Originaire de Kyoto, le cœur culturel du Japon, Aiko a grandi au rythme des jardins de thé et des temples séculaires. Elle conçoit ses assiettes comme des haïkus comestibles, où le vide est aussi important que la matière." }
        };

        function ouvrirHistoire(chef) {
            document.getElementById('story-content').innerHTML = `<h2>${histoires[chef].titre}</h2><p>${histoires[chef].texte}</p>`;
            document.getElementById('chef-overlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function fermerHistoire() {
            document.getElementById('chef-overlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

       // ── LANGUE ──
        function toggleLang(e) {
            e.stopPropagation();
            e.preventDefault();
            const dd = document.getElementById('lang-dropdown');
            dd.classList.toggle('open');
        }

        function setLang(code, e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            applyLang(code);
            document.getElementById('lang-dropdown').classList.remove('open');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.querySelector('.lang-wrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                const dd = document.getElementById('lang-dropdown');
                if (dd) dd.classList.remove('open');
            }
        });
    </script>
</body>
</html>
