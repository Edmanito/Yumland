<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Chargement des produits pour récupérer les noms et prix
$plats = lireJSON(JSON_PLATS)['plats'] ?? [];
$menus = lireJSON(JSON_MENUS)['menus'] ?? [];
$tousLesProduits = array_merge($plats, $menus);

$totalCommande = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Votre Sélection | <?= SITE_NOM ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/panier.css">
</head>
<body class="page-panier">

    <div class="panier-container">
        <header class="panier-header">
            <a href="carte.php" class="back-link">← CONTINUER VOS ACHATS</a>
            <h1>Votre Sélection</h1>
            <div class="gold-line"></div>
        </header>

        <?php if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])): ?>
            <div class="panier-vide">
                <p>Votre panier est aussi pur que le vide.</p>
                <a href="carte.php" class="btn-gold">DÉCOUVRIR LA CARTE</a>
            </div>
        <?php else: ?>
            <table class="panier-table">
                <thead>
                    <tr>
                        <th>PRODUIT</th>
                        <th>PRIX</th>
                        <th>QUANTITÉ</th>
                        <th class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($_SESSION['panier'] as $cle => $item): 
                        // On cherche les infos du produit par son ID
                        $produit = null;
                        foreach ($tousLesProduits as $p) {
                            if ($p['id'] === $item['id']) {
                                $produit = $p;
                                break;
                            }
                        }

                        if ($produit):
                            $sousTotal = $produit['prix'] * $item['qte'];
                            $totalCommande += $sousTotal;
                    ?>
                        <tr>
                            <td>
                                <div class="prod-info">
                                    <span class="prod-name"><?= htmlspecialchars($produit['nom']) ?></span>
                                    <?php if (!empty($item['retraits'])): ?>
                                        <span class="prod-custom">SANS : <?= implode(', ', $item['retraits']) ?></span>
                                    <?php endif; ?>
                                    <a href="modifier_item.php?cle=<?= $cle ?>" class="btn-edit-custom">MODIFIER LA RECETTE</a>
                                </div>
                            </td>
                            <td><?= $produit['prix'] ?>€</td>
                            <td>
                                <div class="qte-picker">
                                    <a href="modifier_panier.php?id=<?= $cle ?>&action=moins" class="btn-qte">-</a>
                                    <span><?= $item['qte'] ?></span>
                                    <a href="modifier_panier.php?id=<?= $cle ?>&action=plus" class="btn-qte">+</a>
                                </div>
                            </td>
                            <td class="text-right">
                                <?= $sousTotal ?>€
                                <a href="modifier_panier.php?id=<?= $cle ?>&action=supprimer" class="btn-delete">✕</a>
                            </td>
                        </tr>
                    <?php endif; endforeach; ?>
                </tbody>
            </table>

            <div class="panier-footer">
                <div class="total-section">
                    <span class="total-label">TOTAL COMMANDE</span>
                    <span class="total-amount"><?= $totalCommande ?>€</span>
                </div>
                <div class="action-buttons">
                    <a href="vider_panier.php" class="btn-outline">VIDER</a>
                    <a href="paiement.php" class="btn-gold">PASSER AU RÈGLEMENT</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>