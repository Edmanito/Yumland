<?php
require_once '../includes/config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

    // On crée une clé unique produit_defaut
    $cle_ligne = $id . "_default";

    if (isset($_SESSION['panier'][$cle_ligne])) {
        $_SESSION['panier'][$cle_ligne]['qte']++;
    } else {
        $_SESSION['panier'][$cle_ligne] = [
            'id' => $id,
            'qte' => 1,
            'retraits' => [] // Pourra etre modifié plus tard via le bouton Personnalisé
        ];
    }
}

header('Location: carte.php?status=success');
exit;