<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

header('Content-Type: application/json');

if (!estConnecte() || !aLeRole('restaurateur')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

$id_cmd = $_GET['id'] ?? '';
$nouveau_statut = $_GET['statut'] ?? '';

if ($id_cmd && $nouveau_statut) {
    $data = lireJSON(JSON_COMMANDES);
    $trouve = false;
    
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id_cmd) {
            $cmd['statut'] = $nouveau_statut;
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

echo json_encode(['success' => false, 'message' => 'Action impossible']);
exit;