<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('livreur');

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: ../php/livraison.php');
    exit;
}

$data = lireJSON(JSON_COMMANDES);
foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $id) {
        $cmd['statut'] = 'livree';
        $cmd['dates']['livraison'] = date('Y-m-d\TH:i:s');
        break;
    }
}

ecrireJSON(JSON_COMMANDES, $data);
header('Location: ../php/livraison.php');
exit;