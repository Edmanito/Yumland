<?php
require_once 'includes/config.php';
require_once 'includes/fonctions.php';

// Redirection automatique si déjà connecté
if (estConnecte()) {
    // On s'assure que la session contient bien les données utilisateur avant le switch
    if (isset($_SESSION['user']['role'])) {
        switch ($_SESSION['user']['role']) {
            case 'admin':
                header('Location: php/admin.php'); exit;
            case 'restaurateur':
                header('Location: php/commande.php'); exit;
            case 'livreur':
                header('Location: php/livraison.php'); exit;
            default:
                header('Location: php/profil.php'); exit;
        }
    }
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
        .auth-error { background: rgba(255,70,70,0.1); border: 1px solid rgba(255,70,70,0.3); color: #ff6b6b; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; }
        .auth-subtitle { color: #888; font-size: 0.85rem; margin-bottom: 20px; display: block; }
        .switch-auth { margin-top: 15px; font-size: 0.8rem; color: #666; }
    </style>
</head>
<body class="page-accueil">

    <div id="side-menu" class="side-panel">
        <div class="menu-content-wrapper">
            <div class="menu-links">
                <a href="javascript:void(0)" onclick="openReservationFromMenu()">RÉSERVER</a>
                <a href="#restaurant" onclick="toggleMenu()">LE RESTAURANT</a>
                <a href="#chefs" onclick="toggleMenu()">LES CHEFS</a>
                <a href="#experience" onclick="toggleMenu()">L'EXPÉRIENCE</a>
                <a href="php/carte.php">LE MENU</a>
                <a href="#informations" onclick="toggleMenu()">INFORMATIONS</a>
            </div>
        </div>
        <div class="menu-footer">
            <div class="footer-main-container">
                <div class="footer-admin-top">
                    <?php if (estConnecte()): ?>
                        <?php if (aLeRole('admin')): ?>
                            <a href="php/admin.php" class="admin-link">ADMINISTRATION</a>
                        <?php elseif (aLeRole('restaurateur')): ?>
                            <a href="php/commande.php" class="admin-link">CUISINE</a>
                        <?php elseif (aLeRole('livreur')): ?>
                            <a href="php/livraison.php" class="admin-link">MES LIVRAISONS</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="footer-main-info">
                    <img src="img/instagram-icon.png" alt="Instagram" class="insta-logo">
                    <span class="separator">|</span>
                    <div class="lang-switcher-menu">FR / EN</div>
                </div>
            </div>
        </div>
    </div>

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
                <h1 class="brand-name">KAISEKI SHUNEI</h1>
            </div>
        </div>

        <div class="header-right">
            <div class="profile-trigger" onclick="gererClicProfil()">
                <img src="img/profil-vide.png" alt="Profil" class="profile-icon-nav">
            </div>
            <a href="javascript:void(0)" class="btn-reservation" onclick="toggleReservation()">COMMANDER</a>
        </div>
    </header>

    <main>
        <section class="hero-section">
            <div class="hero-bg-image"></div>
            <div class="hero-content">
                <h2 class="fade-in">L'Art de la Perfection</h2>
                <div class="search-box">
                    <input type="text" placeholder="Rechercher une saveur..." class="input-search">
                </div>
            </div>
        </section>

        <div id="reservation-panel" class="side-panel-right">
            <div class="close-reservation" onclick="toggleReservation()">✕</div>
            <div class="auth-container">
                <div class="auth-box">
                    <h3>CONNEXION</h3>
                    <span class="auth-subtitle">Accédez à votre espace Kaiseki</span>

                    <?php if ($erreur): ?>
                        <div class="auth-error"><?= htmlspecialchars($erreur) ?></div>
                    <?php endif; ?>

                    <form action="actions/login.php" method="POST">
                        <input type="email" name="login" placeholder="Email" class="input-auth" required>
                        <input type="password" name="mdp" placeholder="Mot de passe" class="input-auth" required>
                        <button type="submit" class="btn-submit">SE CONNECTER</button>
                    </form>
                    <p class="switch-auth">
                        Pas encore de compte ?
                        <a href="php/inscription.php" style="color:var(--gold);text-decoration:none;">S'inscrire</a>
                    </p>
                </div>
            </div>
        </div>

        <section id="restaurant" class="scroll-section restaurant-view">
            <div class="restaurant-bg"></div>
            <div class="blue-overlay"></div>
            <div class="restaurant-content-wrapper">
                <div class="close-gallery-btn" onclick="closeGallery()">✕</div>
                <h2 class="center-title" onclick="openGallery()" style="cursor:pointer">LE RESTAURANT</h2>
                <div class="arrow-container left" onclick="changeImage(-1)">
                    <img src="img/retour.png" class="arrow-icon" style="cursor:pointer" alt="Précédent">
                </div>
                <div id="viewer-container" class="viewer-container">
                    <img src="img/resto-1.png" id="main-photo" alt="Galerie Shunei">
                </div>
                <div class="arrow-container right" onclick="changeImage(1)">
                    <img src="img/retour.png" class="arrow-icon mirror" style="cursor:pointer" alt="Suivant">
                </div>
                <div class="story-bottom-right">
                    <div class="story-box">
                        <span class="section-subtitle">NOTRE HÉRITAGE</span>
                        <p>"Sous l'ombre des cerisiers de Kyoto..."</p>
                    </div>
                </div>
            </div>
        </section>

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

        <section id="menu" class="scroll-section menu-view-minimal">
            <div class="menu-bg-overlay"></div>
            <a href="php/carte.php" class="menu-compact-box">
                <span class="section-subtitle">DÉCOUVRIR</span>
                <h2 class="menu-title-small">LA CARTE</h2>
                <div class="line-gold"></div>
                <p class="click-info">ENTRER DANS L'EXPÉRIENCE</p>
            </a>
        </section>

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
    </main>

    <script src="js/index.js"></script>
    <script>
        function gererClicProfil() {
            // Si tu veux que le clic sur le profil ouvre directement la connexion
            toggleReservation();
        }
    </script>

</body>
</html>