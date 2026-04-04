<?php
// =========================================
// KAISEKI SHUNEI — FONCTIONS.PHP
// =========================================

function lireJSON($fichier) {
    if (!file_exists($fichier)) return [];
    $contenu = file_get_contents($fichier);
    if (!$contenu) return [];
    $data = json_decode($contenu, true);
    return $data ?? [];
}

function ecrireJSON($fichier, $data) {
    return file_put_contents(
        $fichier,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function genererID($prefixe = 'U') {
    return $prefixe . strtoupper(substr(md5(uniqid()), 0, 6));
}

function hasherMotDePasse($mdp) {
    return password_hash($mdp, PASSWORD_DEFAULT);
}

function verifierMotDePasse($mdp, $hash) {
    return password_verify($mdp, $hash);
}

function trouverUtilisateur($login) {
    $data = lireJSON(JSON_USERS);
    if (empty($data['utilisateurs'])) return null;
    foreach ($data['utilisateurs'] as $user) {
        if ($user['login'] === $login) return $user;
    }
    return null;
}

function loginExiste($login) {
    return trouverUtilisateur($login) !== null;
}

function ajouterUtilisateur($nouvelUser) {
    $data = lireJSON(JSON_USERS);
    if (!isset($data['utilisateurs']) || !is_array($data['utilisateurs'])) {
        $data['utilisateurs'] = [];
    }
    $data['utilisateurs'][] = $nouvelUser;
    return ecrireJSON(JSON_USERS, $data);
}

function estConnecte() {
    return isset($_SESSION['user']);
}

function aLeRole($role) {
    if (!estConnecte()) return false;
    return $_SESSION['user']['role'] === $role;
}

function getBaseUrl() {
    // Retourne toujours le chemin absolu vers index.php
    return '/index.php';
}

function requireConnexion() {
    if (!estConnecte()) {
        header('Location: ' . getBaseUrl());
        exit;
    }
}

function requireRole($role) {
    if (!estConnecte()) {
        header('Location: ' . getBaseUrl());
        exit;
    }
    if (!aLeRole($role)) {
        header('Location: ' . getBaseUrl());
        exit;
    }
}

function nettoyer($valeur) {
    return htmlspecialchars(trim($valeur), ENT_QUOTES, 'UTF-8');
}