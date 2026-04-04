<?php
// =========================================
// KAISEKI SHUNEI — ACTIONS/REGISTER.PHP
// =========================================

require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$prenom     = nettoyer($_POST['prenom']     ?? '');
$nom        = nettoyer($_POST['nom']        ?? '');
$login      = nettoyer($_POST['login']      ?? '');
$telephone  = nettoyer($_POST['telephone']  ?? '');
$adresse    = nettoyer($_POST['adresse']    ?? '');
$etage      = nettoyer($_POST['etage']      ?? '');
$interphone = nettoyer($_POST['interphone'] ?? '');
$mdp        = $_POST['mdp'] ?? '';

if (empty($prenom) || empty($nom) || empty($login) || empty($mdp)) {
    header('Location: ../php/inscription.php?erreur=champs_vides');
    exit;
}

if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../php/inscription.php?erreur=email_invalide');
    exit;
}

if (strlen($mdp) < 8) {
    header('Location: ../php/inscription.php?erreur=mdp_court');
    exit;
}

if (loginExiste($login)) {
    header('Location: ../php/inscription.php?erreur=email_existe');
    exit;
}

$nouvelUser = [
    "id"            => genererID('U'),
    "login"         => $login,
    "mot_de_passe"  => hasherMotDePasse($mdp),
    "role"          => "client",
    "statut"        => "actif",
    "premium"       => false,
    "remise"        => 0,
    "infos"         => [
        "nom"        => $nom,
        "prenom"     => $prenom,
        "telephone"  => $telephone,
        "adresse"    => $adresse,
        "etage"      => $etage,
        "interphone" => $interphone
    ],
    "fidelite"      => [
        "points" => 0,
        "badge"  => "BRONZE"
    ],
    "dates"         => [
        "inscription"        => date('Y-m-d'),
        "derniere_connexion"  => date('Y-m-d')
    ]
];

if (ajouterUtilisateur($nouvelUser)) {
    unset($nouvelUser['mot_de_passe']);
    $_SESSION['user'] = $nouvelUser;
    header('Location: ../php/profil.php?success=inscription');
} else {
    header('Location: ../php/inscription.php?erreur=erreur_serveur');
}
exit;