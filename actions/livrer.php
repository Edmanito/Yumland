<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('livreur');

$id = $_GET['id'] ?? '';

if (!$id) {
    header('Location: ../php/livraison.php?erreur=id_manquant');
    exit;
}

$data = lireJSON(JSON_COMMANDES);
$found = false;

if (isset($data['commandes']) && is_array($data['commandes'])) {
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id) {
            $cmd['statut'] = 'livree';
            
            if (!isset($cmd['dates'])) {
                $cmd['dates'] = [];
            }
            $cmd['dates']['livraison'] = date('Y-m-d\TH:i:s');
            
            $found = true;
            break;
        }
    }
}

if ($found) {
    sauvegarderJSON(JSON_COMMANDES, $data);
    header('Location: ../php/livraison.php?success=livraison_terminee');
} else {
    header('Location: ../php/livraison.php?erreur=commande_introuvable');
}

exit;