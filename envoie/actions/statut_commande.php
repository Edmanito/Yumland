<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('restaurateur');

$id     = $_GET['id']     ?? '';
$statut = $_GET['statut'] ?? '';

$statutsValides = ['en_attente', 'en_preparation', 'pret', 'en_livraison', 'livree'];

if (!$id || !in_array($statut, $statutsValides)) {
    header('Location: ../php/commande.php');
    exit;
}

$data = lireJSON(JSON_COMMANDES);
foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $id) {
        $cmd['statut'] = $statut;
        // Mettre à jour les dates selon le statut
        if ($statut === 'en_preparation') {
            $cmd['dates']['preparation'] = date('Y-m-d\TH:i:s');
        } elseif ($statut === 'livree') {
            $cmd['dates']['livraison'] = date('Y-m-d\TH:i:s');
        }
        break;
    }
}

ecrireJSON(JSON_COMMANDES, $data);
header('Location: ../php/commande.php');
exit;