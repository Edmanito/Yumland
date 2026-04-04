<?php
// =========================================
// KAISEKI SHUNEI — FONCTIONS.PHP
// =========================================

/**
 * Lit un fichier JSON et retourne son contenu sous forme de tableau
 */
function lireJSON($fichier) {
    if (!file_exists($fichier)) return [];
    $contenu = file_get_contents($fichier);
    if (!$contenu) return [];
    $data = json_decode($contenu, true);
    return $data ?? [];
}

/**
 * Écrit des données dans un fichier JSON (Alias de sauvegarderJSON pour compatibilité)
 */
function ecrireJSON($fichier, $data) {
    return file_put_contents(
        $fichier,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Sauvegarde des données dans un fichier JSON (Utilisée dans retour_paiement.php)
 */
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

// --- SYSTÈME DE CONNEXION ET RÔLES ---

function estConnecte() {
    return isset($_SESSION['user']);
}

/**
 * Vérifie si l'utilisateur a un rôle précis. 
 * L'admin a accès à tout par défaut.
 */
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

/**
 * Restreint l'accès à un rôle spécifique
 */
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