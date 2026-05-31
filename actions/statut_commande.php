<?php
// =========================================
// KAISEKI SHUNEI — ACTIONS/STATUT_COMMANDE.PHP
// Action AJAX : changement de statut d'une commande.
// =========================================

require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// On indique au navigateur que la réponse est du JSON
// (important pour que commande.js puisse la parser avec response.json())
header('Content-Type: application/json');

// SÉCURITÉ : seuls les restaurateurs (et admins) peuvent changer les statuts
// Si non connecté ou mauvais rôle → refus immédiat
if (!estConnecte() || !aLeRole('restaurateur')) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

// Récupération des paramètres GET envoyés par commande.js
$id_cmd        = $_GET['id']     ?? '';
$nouveau_statut = $_GET['statut'] ?? '';

if ($id_cmd && $nouveau_statut) {
    // Chargement de toutes les commandes depuis le JSON
    $data = lireJSON(JSON_COMMANDES);
    $trouve = false;

    // Recherche de la commande par son ID et mise à jour du statut
    // Le & dans foreach() est crucial : il passe $cmd par référence
    // Sans lui, la modification ne serait pas sauvegardée dans $data
    foreach ($data['commandes'] as &$cmd) {
        if ($cmd['id'] === $id_cmd) {
            $cmd['statut'] = $nouveau_statut;
            $trouve = true;
            break;
        }
    }

    if ($trouve) {
        // Sauvegarde du JSON mis à jour sur le disque
        sauvegarderJSON(JSON_COMMANDES, $data);
        // Réponse JSON positive → commande.js déplace la carte visuellement
        echo json_encode(['success' => true]);
        exit;
    }
}

// Si la commande n'a pas été trouvée ou les paramètres sont manquants
echo json_encode(['success' => false, 'message' => 'Action impossible']);
exit;