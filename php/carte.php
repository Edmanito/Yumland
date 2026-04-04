<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Chargement des données
$dataPlats = lireJSON(JSON_PLATS);
$plats = $dataPlats['plats'] ?? [];

$dataMenus = lireJSON(JSON_MENUS);
$menus = $dataMenus['menus'] ?? [];

// Calcul du nombre d'articles dans le panier (nouvelle structure de session)
$panierCount = 0;
if(isset($_SESSION['panier'])) {
    foreach($_SESSION['panier'] as $item) { 
        $panierCount += $item['qte']; 
    }
}

// Définition des catégories
$categories = [
    'entree'  => ['titre' => 'Otsumami', 'desc' => 'Entrées délicates', 'num' => '01'],
    'sushi'   => ['titre' => 'Nigiri & Sashimi', 'desc' => 'La pureté du produit', 'num' => '02'],
    'plat'    => ['titre' => 'Signatures', 'desc' => 'Haute gastronomie', 'num' => '03'],
    'dessert' => ['titre' => 'Sweets', 'desc' => 'Notes finales', 'num' => '04'],
    'boisson' => ['titre' => 'Beverages', 'desc' => 'Thés et Sakés', 'num' => '05']
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Carte | <?= SITE_NOM ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Plus+Jakarta+Sans:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/carte.css">
</head>
<body class="page-menu">

    <div class="header-actions">
        <a href="panier.php" class="btn-cart-nav">
            PANIER 
            <?php if($panierCount > 0): ?>
                <span class="cart-badge"><?= $panierCount ?></span>
            <?php endif; ?>
        </a>
    </div>

    <a href="../index.php" class="floating-back-btn">
        <span class="arrow">←</span> <span class="text">ACCUEIL</span>
    </a>

    <header class="menu-hero">
        <div class="hero-content">
            <span class="pre-title">MAÎTRISE & TRADITION</span>
            <h1 class="glitch-title">La Carte</h1>
            <div class="search-container">
                <div class="search-inner">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="menuSearch" placeholder="Rechercher une saveur...">
                </div>
            </div>
        </div>
    </header>

    <main class="menu-wrapper">
        
        <section class="menu-section">
            <div class="category-header">
                <h2>Nos Menus Configurés</h2>
            </div>
            <div class="items-grid">
                <?php foreach($menus as $m): ?>
                <article class="dish-card signature">
                    <div class="dish-content">
                        <div class="dish-main-info">
                            <h3><?= htmlspecialchars($m['nom']) ?></h3>
                            <span class="price"><?= $m['prix'] ?>€</span>
                        </div>
                        <p class="dish-desc"><?= htmlspecialchars($m['description']) ?></p>
                        <a href="ajouter_panier.php?id=<?= $m['id'] ?>" class="btn-add-cart">AJOUTER AU PANIER</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <?php foreach($categories as $code => $info): 
            $platsFiltres = array_filter($plats, fn($p) => $p['categorie'] === $code);
        ?>
        <section class="dish-category">
            <div class="category-header">
                <div class="cat-meta">
                    <span class="cat-num"><?= $info['num'] ?></span>
                    <div class="cat-line"></div>
                </div>
                <h2><?= $info['titre'] ?></h2>
                <p class="cat-desc"><?= $info['desc'] ?></p>
            </div>
            
            <div class="items-grid">
                <?php foreach($platsFiltres as $p): ?>
                <article class="dish-card" data-title="<?= htmlspecialchars($p['nom']) ?>">
                    <div class="dish-img-container">
                        <?php $src = !empty($p['image']) ? "../" . $p['image'] : "../img/menu/default.png"; ?>
                        <img src="<?= $src ?>" alt="<?= htmlspecialchars($p['nom']) ?>">
                        <div class="zoom-overlay"><span>VOIR PLUS</span></div>
                    </div>
                    <div class="dish-content">
                        <div class="dish-main-info">
                            <h3><?= htmlspecialchars($p['nom']) ?></h3>
                            <span class="price"><?= $p['prix'] ?>€</span>
                        </div>
                        <p class="dish-desc"><?= htmlspecialchars($p['description']) ?></p>
                        <a href="ajouter_panier.php?id=<?= $p['id'] ?>" class="btn-add-cart">AJOUTER AU PANIER</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>

    </main>

    <script src="../js/carte.js"></script>
</body>
</html>