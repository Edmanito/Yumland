<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

$dataPlats = lireJSON(JSON_PLATS);
$plats = $dataPlats['plats'] ?? [];

$dataMenus = lireJSON(JSON_MENUS);
$menus = $dataMenus['menus'] ?? [];

$panierCount = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $panierCount += $item['qte'];
    }
}

$categories = [
    'entree'  => ['titre' => 'Entrées',           'desc' => 'Amuse-bouches & Entrées délicates', 'num' => '01', 'kanji' => '前菜', 'layout' => 'layout-2'],
    'sushi'   => ['titre' => 'Nigiri & Sashimi',  'desc' => 'La pureté du produit',              'num' => '02', 'kanji' => '寿司', 'layout' => 'layout-3'],
    'plat'    => ['titre' => 'Plats Signatures',   'desc' => 'Haute gastronomie japonaise',       'num' => '03', 'kanji' => '料理', 'layout' => 'layout-2'],
    'dessert' => ['titre' => 'Desserts',           'desc' => 'Notes finales & Douceurs',          'num' => '04', 'kanji' => '甘味', 'layout' => 'layout-3'],
    'boisson' => ['titre' => 'Boissons',           'desc' => 'Sakés, Thés & Spiritueux',         'num' => '05', 'kanji' => '飲み物', 'layout' => 'layout-drinks'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Carte | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Josefin+Sans:wght@100;200;300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/carte.css">
</head>
<body class="page-menu">

    <!-- ── NAVIGATION ── -->
    <nav class="nav-top">
        <a href="../index.php" class="nav-back">
            <span class="arrow"></span>
            <span>ACCUEIL</span>
        </a>
        <span class="nav-brand">KAISEKI SHUNEI</span>
        <div class="nav-right">
            <a href="panier.php" class="btn-cart">
                MON PANIER
                <?php if ($panierCount > 0): ?>
                    <span class="cart-count"><?= $panierCount ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <!-- ── HERO ── -->
    <section class="carte-hero">
        <div class="hero-bg"></div>
        <div class="hero-kanji">春栄製</div>
        <div class="hero-content">
            <div class="hero-eyebrow">
                <div class="hero-line"></div>
                <span>MAÎTRISE & TRADITION</span>
            </div>
            <h1 class="hero-title">La <em>Carte</em></h1>
            <div class="search-bar">
                <input type="text" id="menuSearch" placeholder="Rechercher une saveur...">
                <span class="search-icon">✦</span>
            </div>
        </div>
    </section>

    <!-- ── NAVIGATION CATÉGORIES ── -->
    <nav class="cat-nav">
        <button class="cat-nav-btn active" data-target="section-menus">MENUS</button>
        <?php foreach ($categories as $code => $info): ?>
            <button class="cat-nav-btn" data-target="section-<?= $code ?>">
                <?= strtoupper($info['titre']) ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- ── CONTENU PRINCIPAL ── -->
    <main class="carte-main">

        <!-- MENUS CONFIGURÉS -->
        <?php if (!empty($menus)): ?>
        <section class="menus-section cat-section" id="section-menus">
            <div class="cat-header">
                <div class="cat-num-block">
                    <span class="cat-num">00</span>
                </div>
                <div class="cat-title-block">
                    <h2>Menus Kaiseki</h2>
                    <p>EXPÉRIENCES COMPLÈTES & DÉGUSTATION</p>
                </div>
                <span class="cat-kanji">懐石</span>
            </div>
            <div class="menus-grid">
                <?php foreach ($menus as $m): ?>
                <article class="menu-card">
                    <div class="menu-card-top">
                        <h3><?= htmlspecialchars($m['nom']) ?></h3>
                        <span class="price"><?= $m['prix_total'] ?>€</span>
                    </div>
                    <p><?= htmlspecialchars($m['description']) ?></p>
                    <a href="ajouter_panier.php?id=<?= $m['id'] ?>" class="btn-menu">RÉSERVER CE MENU</a>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- CATÉGORIES DE PLATS -->
        <?php foreach ($categories as $code => $info):
            $platsFiltres = array_filter($plats, fn($p) => ($p['categorie'] ?? '') === $code && ($p['disponible'] ?? true));
            if (empty($platsFiltres)) continue;
        ?>
        <section class="cat-section" id="section-<?= $code ?>">
            <div class="cat-header">
                <div class="cat-num-block">
                    <span class="cat-num"><?= $info['num'] ?></span>
                </div>
                <div class="cat-title-block">
                    <h2><?= $info['titre'] ?></h2>
                    <p><?= strtoupper($info['desc']) ?></p>
                </div>
                <span class="cat-kanji"><?= $info['kanji'] ?></span>
            </div>

            <?php if ($code === 'boisson'): ?>
            <!-- BOISSONS -->
            <div class="dishes-grid <?= $info['layout'] ?>">
                <?php foreach ($platsFiltres as $p):
                    $icon = '🍶';
                    $nom_lower = strtolower($p['nom']);
                    if (str_contains($nom_lower, 'whisky'))     $icon = '🥃';
                    elseif (str_contains($nom_lower, 'thé'))    $icon = '🍵';
                    elseif (str_contains($nom_lower, 'eau'))    $icon = '💧';
                    elseif (str_contains($nom_lower, 'champagne')) $icon = '🥂';
                    elseif (str_contains($nom_lower, 'highball'))  $icon = '🍹';
                ?>
                <article class="drink-card">
                    <div class="drink-icon"><?= $icon ?></div>
                    <div class="drink-name"><?= htmlspecialchars($p['nom']) ?></div>
                    <div class="drink-desc"><?= htmlspecialchars($p['description']) ?></div>
                    <div class="drink-footer">
                        <span class="drink-price"><?= $p['prix'] ?>€</span>
                        <a href="ajouter_panier.php?id=<?= $p['id'] ?>" class="drink-btn">COMMANDER</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php else: ?>
            <!-- PLATS -->
            <div class="dishes-grid <?= $info['layout'] ?>">
                <?php foreach ($platsFiltres as $p):
                    $src = !empty($p['image']) ? '../' . $p['image'] : null;
                    $isPremium = $p['prix'] >= 60;
                ?>
                <article class="dish-card"
                    data-title="<?= htmlspecialchars($p['nom']) ?>"
                    data-img="<?= $src ?? '' ?>">

                    <?php if ($src): ?>
                    <div class="dish-img-wrap">
                        <img src="<?= $src ?>" alt="<?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                        <div class="dish-overlay"></div>
                        <?php if ($isPremium): ?>
                            <span class="dish-badge">SIGNATURE</span>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="dish-no-img">
                        <span class="placeholder-kanji"><?= $info['kanji'] ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="dish-body">
                        <div class="dish-top">
                            <h3 class="dish-name"><?= htmlspecialchars($p['nom']) ?></h3>
                            <span class="dish-price"><?= $p['prix'] ?>€</span>
                        </div>
                        <p class="dish-desc"><?= htmlspecialchars($p['description']) ?></p>

                        <?php if (!empty($p['allergenes'])): ?>
                        <div class="dish-allergenes">
                            <?php foreach ($p['allergenes'] as $a): ?>
                                <span class="allergen-tag"><?= htmlspecialchars($a) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <a href="ajouter_panier.php?id=<?= $p['id'] ?>" class="btn-ajouter">AJOUTER AU PANIER</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </section>
        <?php endforeach; ?>

    </main>

    <!-- ── PIED DE PAGE ── -->
    <footer class="carte-footer">
        <p>© 2026 KAISEKI SHUNEI — TOUS DROITS RÉSERVÉS</p>
        <p>PRIX EN EUROS, SERVICE INCLUS — LISTE DES ALLERGÈNES DISPONIBLE SUR DEMANDE</p>
    </footer>

    <!-- ── FENÊTRE ZOOM IMAGE ── -->
    <div class="image-modal" id="imageModal">
        <button class="modal-close">✕ FERMER</button>
        <img class="modal-img" id="modalImg" src="" alt="">
        <p class="modal-caption" id="modalCaption"></p>
    </div>

    <script src="../js/carte.js"></script>
</body>
</html>