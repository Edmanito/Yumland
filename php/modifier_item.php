<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

$cle = $_GET['cle'] ?? null;
if (!$cle || !isset($_SESSION['panier'][$cle])) {
    header('Location: panier.php');
    exit;
}

$item_panier = $_SESSION['panier'][$cle];
$plats = lireJSON(JSON_PLATS)['plats'] ?? [];
$menus = lireJSON(JSON_MENUS)['menus'] ?? [];
$tous = array_merge($plats, $menus);

$produit = null;
foreach ($tous as $p) {
    if ($p['id'] === $item_panier['id']) {
        $produit = $p;
        break;
    }
}

$ingredients = explode(',', str_replace('.', '', $produit['description']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['panier'][$cle]['retraits'] = $_POST['retraits'] ?? [];
    header('Location: panier.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Personnaliser | <?= SITE_NOM ?></title>
    <link rel="stylesheet" href="../css/panier.css">
    <style>
        .custom-box { background: #0a0a0a; border: 1px solid #bc9c64; padding: 30px; max-width: 450px; margin: 100px auto; border-radius: 4px; text-align: center; }
        .ing-list { text-align: left; margin: 20px 0; }
        .ing-item { display: block; padding: 10px; border-bottom: 1px solid #222; cursor: pointer; color: #fff; }
        .ing-item input { margin-right: 15px; accent-color: #bc9c64; scale: 1.3; }
        h2 { color: #bc9c64; font-family: 'Playfair Display'; margin-bottom: 10px; }
    </style>
</head>
<body style="background: #000; color: #eee; font-family: 'Plus Jakarta Sans';">
    <div class="custom-box">
        <h2><?= htmlspecialchars($produit['nom']) ?></h2>
        <p style="font-size: 0.9em; opacity: 0.7;">Cochez les éléments à retirer de la recette :</p>
        
        <form method="POST">
            <div class="ing-list">
                <?php foreach($ingredients as $ing): $ing = trim($ing); ?>
                <label class="ing-item">
                    <input type="checkbox" name="retraits[]" value="<?= $ing ?>" 
                        <?= in_array($ing, $item_panier['retraits']) ? 'checked' : '' ?>>
                    SANS <?= strtoupper($ing) ?>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn-gold" style="width:100%; border:none; padding:15px; cursor:pointer;">ENREGISTRER</button>
            <a href="panier.php" style="display:block; margin-top:15px; color:#666; text-decoration:none; font-size:0.8em;">ANNULER</a>
        </form>
    </div>
</body>
</html>