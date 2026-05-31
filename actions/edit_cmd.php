<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

header('Content-Type: application/json');

// Vérification du statut de l'utilisateur en session
if (isset($_SESSION['user'])) {
    $allUsersData = lireJSON(JSON_USERS);
    $currentUserFromDB = null;
    foreach ($allUsersData['utilisateurs'] as $u) {
        if ($u['id'] === $_SESSION['user']['id']) {
            $currentUserFromDB = $u;
            break;
        }
    }

    if ($currentUserFromDB && $currentUserFromDB['statut'] === 'suspendu') {
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Votre compte a été suspendu.']);
        exit;
    }
    $_SESSION['user'] = $currentUserFromDB; // Rafraîchissement des données de session
}
// Vérifier que l'utilisateur est bien connecté
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

// Récupérer le corps de la requête JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id_commande']) || !isset($input['articles'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$id_commande = $input['id_commande'];
$nouveaux_articles = $input['articles'];

// Charger les commandes
$dataCommandes = lireJSON(JSON_COMMANDES);
$commandes = &$dataCommandes['commandes'];
$commandeTrouvee = false;

foreach ($commandes as &$cmd) {
    if ($cmd['id'] === $id_commande && $cmd['id_client'] === $_SESSION['user']['id']) {
        // Vérifier si le statut permet la modification
        if ($cmd['statut'] !== 'en_attente') {
            echo json_encode(['success' => false, 'message' => 'Cette commande est déjà en préparation.']);
            exit;
        }

        $ancien_total = $cmd['prix_total'];
        
        // Calculer le nouveau total
        $nouveau_total = 0;
        foreach ($nouveaux_articles as $art) {
            $nouveau_total += ($art['quantite'] * $art['prix_unitaire']);
        }

        // Mettre à jour la commande
        $cmd['articles'] = $nouveaux_articles;
        $cmd['prix_total'] = $nouveau_total;

        // --- NOUVEAU : GESTION DE L'ANNULATION ---
        if (empty($nouveaux_articles) || $nouveau_total == 0) {
            $cmd['statut'] = 'annulee';
        } else {
            $cmd['statut'] = 'en_attente';
        }

        // Gestion de la différence de prix (le cahier des charges)
        if ($nouveau_total > $ancien_total) {
            // Cas d'un nouveau total supérieur : passage en paiement partiel
            // Mise à jour du statut de paiement
            $cmd['paiement']['statut'] = 'partiel'; 
        } 
        else if ($nouveau_total < $ancien_total) {
            // La commande est moins chère : on génère un ticket de réduction !
            $difference = $ancien_total - $nouveau_total;
            
            // On ajoute le ticket de réduction à l'utilisateur
            $dataUsers = lireJSON(JSON_USERS);
            foreach ($dataUsers['utilisateurs'] as &$u) {
                if ($u['id'] === $_SESSION['user']['id']) {
                    if (!isset($u['tickets_reduction'])) {
                        $u['tickets_reduction'] = [];
                    }
                    $u['tickets_reduction'][] = [
                        'montant' => $difference,
                        'origine' => 'Remboursement CMD ' . $id_commande
                    ];
                    // Mise à jour de la session
                    $_SESSION['user'] = $u; 
                    break;
                }
            }
            sauvegarderJSON(JSON_USERS, $dataUsers);
        }

        $commandeTrouvee = true;
        break;
    }
}

if ($commandeTrouvee) {
    sauvegarderJSON(JSON_COMMANDES, $dataCommandes);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable ou vous n\'avez pas les droits.']);
}