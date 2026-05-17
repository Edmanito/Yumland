<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// TRÈS IMPORTANT : On dit au navigateur qu'on envoie du JSON (évite la page blanche)
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
    
    // 1. On cherche si c'est un menu
    if (!empty($dataMenus['menus'])) {
        foreach ($dataMenus['menus'] as $menu) {
            if ($menu['id'] == $id_produit) {
                $produit_trouve = ['id' => $menu['id'], 'nom' => $menu['nom'], 'prix' => $menu['prix_total'], 'qte' => 1];
                break;
            }
        }
    }
    
    // 2. Si pas trouvé, on cherche dans les plats
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

        // 3. On calcule le total pour mettre à jour la pastille rouge
        $totalCount = 0;
        foreach ($_SESSION['panier'] as $item) {
            $totalCount += $item['qte'];
        }

        // On renvoie la réponse au JavaScript
        echo json_encode(['success' => true, 'total_items' => $totalCount]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
exit;