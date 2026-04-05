<?php
require_once '../includes/config.php';

$cle = $_GET['id'] ?? null; 
$action = $_GET['action'] ?? null;

if ($cle && isset($_SESSION['panier'][$cle])) {
    switch ($action) {
        case 'plus':
            $_SESSION['panier'][$cle]['qte']++;
            break;

        case 'moins':
            $_SESSION['panier'][$cle]['qte']--;
            if ($_SESSION['panier'][$cle]['qte'] <= 0) {
                unset($_SESSION['panier'][$cle]);
            }
            break;

        case 'supprimer':
            unset($_SESSION['panier'][$cle]);
            break;
    }
}

header('Location: panier.php');
exit;