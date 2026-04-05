<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (!estConnecte() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$idCmd         = nettoyer($_POST['cmd'] ?? '');
$noteProduits  = (int)($_POST['note_produits'] ?? 0);
$noteLivraison = (int)($_POST['note_livraison'] ?? 0);
$commentaire   = nettoyer($_POST['commentaire'] ?? '');

if (empty($idCmd)) {
    header('Location: ../php/profil.php?erreur=id_manquant');
    exit;
}

$data = lireJSON(JSON_COMMANDES);
$trouve = false;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $idCmd && $cmd['id_client'] === $_SESSION['user']['id']) {
        
        $cmd['note_client'] = [
            'produits'    => $noteProduits,
            'livraison'   => $noteLivraison,
            'commentaire' => $commentaire,
            'date_note'   => date('Y-m-d H:i:s')
        ];
        $trouve = true;
        break;
    }
}

if ($trouve) {
    sauvegarderJSON(JSON_COMMANDES, $data);
    header('Location: ../php/remerciements.php');
} else {
    header('Location: ../php/profil.php?erreur=maj_impossible');
}
exit;