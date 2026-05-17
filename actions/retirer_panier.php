<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['id']) && isset($_SESSION['panier'])) {
    $id_produit = $_GET['id'];
    
    // On cherche la clé exacte (id + suffixe default)
    $cle_ligne = $id_produit . "_default";

    if (isset($_SESSION['panier'][$cle_ligne])) {
        // On vérifie si la quantité est supérieure à 1
        if ($_SESSION['panier'][$cle_ligne]['qte'] > 1) {
            $_SESSION['panier'][$cle_ligne]['qte'] -= 1;
        } else {
            // Sinon on supprime la ligne complète
            unset($_SESSION['panier'][$cle_ligne]);
        }
    }
}

// On redirige toujours vers le panier car cette action 
// est généralement appelée depuis la page panier.php
header('Location: ../php/panier.php');
exit();