<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireConnexion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../php/profil.php');
    exit;
}

$idCommande    = nettoyer($_POST['cmd']           ?? '');
$noteLivraison = intval($_POST['note_livraison']  ?? 0);
$noteProduits  = intval($_POST['note_produits']   ?? 0);
$commentaire   = nettoyer($_POST['commentaire']   ?? '');

if (!$idCommande || $noteLivraison < 1 || $noteLivraison > 5 || $noteProduits < 1 || $noteProduits > 5) {
    header('Location: ../php/profil.php?erreur=note_invalide');
    exit;
}

$userId = $_SESSION['user']['id'];
$data = lireJSON(JSON_COMMANDES);
$found = false;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $idCommande && $cmd['id_client'] === $userId && $cmd['statut'] === 'livree') {
        $cmd['note_client'] = [
            'note_livraison' => $noteLivraison,
            'note_produits'  => $noteProduits,
            'commentaire'    => $commentaire,
        ];
        $found = true;
        break;
    }
}

if (!$found) {
    header('Location: ../php/profil.php?erreur=commande_introuvable');
    exit;
}

ecrireJSON(JSON_COMMANDES, $data);
header('Location: ../php/profil.php?success=note_envoyee');
exit;