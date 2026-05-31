<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Définition de l'en-tête pour une réponse au format JSON
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$id_produit = $_GET['id'] ?? null;

if ($id_produit) {
    $dataPlats = lireJSON(JSON_PLATS);
    $dataMenus = lireJSON(JSON_MENUS);
    
    $produit_trouve = null;
    
    // 1. Recherche dans les menus
    if (!empty($dataMenus['menus'])) {
        foreach ($dataMenus['menus'] as $menu) {
            if ($menu['id'] == $id_produit) {
                $produit_trouve = ['id' => $menu['id'], 'nom' => $menu['nom'], 'prix' => $menu['prix_total'], 'qte' => 1];
                break;
            }
        }
    }
    
    // 2. Recherche dans les plats si non trouvé précédemment
    if (!$produit_trouve && !empty($dataPlats['plats'])) {
        foreach ($dataPlats['plats'] as $plat) {
            if ($plat['id'] == $id_produit) {
                $produit_trouve = ['id' => $plat['id'], 'nom' => $plat['nom'], 'prix' => $plat['prix'], 'qte' => 1];
                break;
            }
        }
    }
    
    if ($produit_trouve) {
        $cle_ligne = $id_produit . "_default";
        
        if (isset($_SESSION['panier'][$cle_ligne])) {
            $_SESSION['panier'][$cle_ligne]['qte'] += 1;
        } else {
            $_SESSION['panier'][$cle_ligne] = $produit_trouve;
        }

        // 3. Calcul du total pour mise à jour de l'indicateur visuel
        $totalCount = 0;
        foreach ($_SESSION['panier'] as $item) {
            $totalCount += $item['qte'];
        }

        // Retour de la réponse au script JavaScript
        echo json_encode(['success' => true, 'total_items' => $totalCount]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
exit;