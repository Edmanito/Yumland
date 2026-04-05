<?php
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

$mdpOk = false;
if (str_starts_with($user['mot_de_passe'], '$2y$')) {
    $mdpOk = password_verify($mdp, $user['mot_de_passe']);
} else {
    $mdpOk = ($mdp === $user['mot_de_passe']);
}

if (!$mdpOk) {
    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}

if ($user['statut'] === 'suspendu') {
    header('Location: ../index.php?erreur=compte_suspendu');
    exit;
}

$data = lireJSON(JSON_USERS);
foreach ($data['utilisateurs'] as &$u) {
    if ($u['login'] === $login) {
        $u['dates']['derniere_connexion'] = date('Y-m-d');
        break;
    }
}
ecrireJSON(JSON_USERS, $data);

unset($user['mot_de_passe']);
$_SESSION['user'] = $user;

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