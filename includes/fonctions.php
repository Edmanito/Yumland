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


function sauvegarderJSON($fichier, $data) {
    return ecrireJSON($fichier, $data);
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
    $userRole = $_SESSION['user']['role'];
    return ($userRole === $role || $userRole === 'admin');
}

function requireConnexion() {
    if (!estConnecte()) {
        $profondeur = substr_count($_SERVER['PHP_SELF'], '/');
        $redirect = $profondeur > 2 ? '../index.php' : 'index.php';
        header('Location: ' . $redirect);
        exit;
    }
}


function requireRole($role) {
    if (!estConnecte() || !aLeRole($role)) {
        $profondeur = substr_count($_SERVER['PHP_SELF'], '/');
        $redirect = $profondeur > 2 ? '../index.php' : 'index.php';
        header('Location: ' . $redirect . '?erreur=acces_refuse');
        exit;
    }
}

function nettoyer($valeur) {
    return htmlspecialchars(trim($valeur), ENT_QUOTES, 'UTF-8');
}