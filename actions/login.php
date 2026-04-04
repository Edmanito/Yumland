<?php
// =========================================
// KAISEKI SHUNEI — ACTIONS/LOGIN.PHP
// =========================================

require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$login = nettoyer($_POST['login'] ?? '');
$mdp   = $_POST['mdp'] ?? '';

if (empty($login) || empty($mdp)) {
    header('Location: ../index.php?erreur=champs_vides');
    exit;
}

$user = trouverUtilisateur($login);

if (!$user) {
    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}

if (!verifierMotDePasse($mdp, $user['mot_de_passe'])) {
    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}

if ($user['statut'] === 'suspendu') {
    header('Location: ../index.php?erreur=compte_suspendu');
    exit;
}

// Mettre à jour la dernière connexion
$data = lireJSON(JSON_USERS);
foreach ($data['utilisateurs'] as &$u) {
    if ($u['login'] === $login) {
        $u['dates']['derniere_connexion'] = date('Y-m-d');
        break;
    }
}
ecrireJSON(JSON_USERS, $data);

// Créer la session
unset($user['mot_de_passe']);
$_SESSION['user'] = $user;

// Rediriger selon le rôle
switch ($user['role']) {
    case 'admin':
        header('Location: ../php/admin.php');
        break;
    case 'restaurateur':
        header('Location: ../php/commande.php');
        break;
    case 'livreur':
        header('Location: ../php/livraison.php');
        break;
    default:
        header('Location: ../php/profil.php');
        break;
}
exit;