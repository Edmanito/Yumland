<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Sécurité : Vérifier que l'utilisateur est connecté et que c'est du POST
if (!estConnecte() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// 1. Récupération des données du formulaire
$idCmd         = nettoyer($_POST['cmd'] ?? '');
$noteProduits  = (int)($_POST['note_produits'] ?? 0);
$noteLivraison = (int)($_POST['note_livraison'] ?? 0);
$commentaire   = nettoyer($_POST['commentaire'] ?? '');

if (empty($idCmd)) {
    header('Location: ../php/profil.php?erreur=id_manquant');
    exit;
}

// 2. Chargement des commandes
$data = lireJSON(JSON_COMMANDES);
$trouve = false;

// 3. Recherche et mise à jour de la commande
foreach ($data['commandes'] as &$cmd) {
    // On vérifie l'ID et que la commande appartient bien à l'utilisateur connecté
    if ($cmd['id'] === $idCmd && $cmd['id_client'] === $_SESSION['user']['id']) {
        
        // On enregistre la note sous forme d'un petit tableau
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

// 4. Sauvegarde et redirection
if ($trouve) {
    sauvegarderJSON(JSON_COMMANDES, $data);
    // Redirection vers ta nouvelle page de remerciements !
    header('Location: ../php/remerciements.php');
} else {
    header('Location: ../php/profil.php?erreur=maj_impossible');
}
exit;