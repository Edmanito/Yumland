<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Sécurité de base
if (!estConnecte() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Récupération des données
$idCmd         = nettoyer($_POST['cmd'] ?? '');
$noteProduits  = (int)($_POST['note_produits'] ?? 0);
$noteLivraison = (int)($_POST['note_livraison'] ?? 0);
$commentaire   = nettoyer($_POST['commentaire'] ?? '');

// 🛡️ SÉCURITÉ : On vérifie que les notes sont bien entre 1 et 5
if (empty($idCmd) || $noteProduits < 1 || $noteProduits > 5 || $noteLivraison < 1 || $noteLivraison > 5) {
    header('Location: ../php/profil.php?erreur=note_invalide');
    exit;
}

$data = lireJSON(JSON_COMMANDES);
$trouve = false;

foreach ($data['commandes'] as &$cmd) {
    // Vérification stricte : bonne commande + bon client + statut OBLIGATOIREMENT "livree"
    if ($cmd['id'] === $idCmd && $cmd['id_client'] === $_SESSION['user']['id'] && $cmd['statut'] === 'livree') {
        
        // 🔑 LES BONNES CLÉS pour que ton "oeil" javascript génère bien les étoiles
        $cmd['note_client'] = [
            'produits'    => $noteProduits,
            'livraison'   => $noteLivraison,
            'commentaire' => $commentaire,
            'date_note'   => date('d/m/Y') // Date formatée à la française
        ];
        $trouve = true;
        break;
    }
}

if ($trouve) {
    // On utilise TA bonne fonction d'écriture (comme dans le Fichier 1)
    ecrireJSON(JSON_COMMANDES, $data);
    header('Location: ../php/profil.php?success=note_envoyee');
} else {
    header('Location: ../php/profil.php?erreur=commande_introuvable');
}
exit;