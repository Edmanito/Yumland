<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('restaurateur');

$id_cmd = $_POST['id_commande'] ?? '';
$id_livreur = $_POST['id_livreur'] ?? '';

if ($id_cmd && $id_livreur) {
    $data = lireJSON(JSON_COMMANDES);
    
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id_cmd) {
            $cmd['id_livreur'] = $id_livreur; 
            $cmd['statut'] = 'en_livraison';  
            $cmd['dates']['expedition'] = date('Y-m-d\TH:i:s');
            break;
        }
    }
    
    sauvegarderJSON(JSON_COMMANDES, $data);
}

header('Location: ../php/commande.php?success=assigne');
exit;