<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Sécurité : Seul un livreur peut valider une livraison
requireRole('livreur');

$id = $_GET['id'] ?? '';

if (!$id) {
    header('Location: ../php/livraison.php?erreur=id_manquant');
    exit;
}

// Chargement des commandes
$data = lireJSON(JSON_COMMANDES);
$found = false;

if (isset($data['commandes']) && is_array($data['commandes'])) {
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id) {
            // Mise à jour du statut
            $cmd['statut'] = 'livree';
            
            // On s'assure que la structure 'dates' existe avant d'écrire dedans
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
    // On utilise sauvegarderJSON (qui est l'alias de ecrireJSON dans ton fonctions.php)
    sauvegarderJSON(JSON_COMMANDES, $data);
    // Redirection avec un petit message de succès
    header('Location: ../php/livraison.php?success=livraison_terminee');
} else {
    header('Location: ../php/livraison.php?erreur=commande_introuvable');
}

exit;