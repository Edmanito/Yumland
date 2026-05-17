<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Très important : on prévient le navigateur qu'on parle en JSON
header('Content-Type: application/json');

// Sécurité : On vérifie que c'est bien le restaurateur
if (!estConnecte() || !aLeRole('restaurateur')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$id_cmd = $_POST['id_commande'] ?? '';
$id_livreur = $_POST['id_livreur'] ?? '';

if ($id_cmd && $id_livreur) {
    $data = lireJSON(JSON_COMMANDES);
    $trouve = false;
    
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id_cmd) {
            $cmd['id_livreur'] = $id_livreur; 
            $cmd['statut'] = 'en_livraison';  
            $cmd['dates']['expedition'] = date('Y-m-d\TH:i:s');
            $trouve = true;
            break;
        }
    }
    
    if ($trouve) {
        sauvegarderJSON(JSON_COMMANDES, $data);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Commande introuvable ou données manquantes']);
exit;