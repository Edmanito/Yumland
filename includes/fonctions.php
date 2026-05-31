<?php
// =========================================
// KAISEKI SHUNEI — FONCTIONS.PHP
// Fichier central contenant toutes les
// fonctions utilitaires du projet.
// Inclus sur chaque page via require_once.
// =========================================


// -----------------------------------------
// LECTURE / ÉCRITURE JSON
// Ces fonctions remplacent une base de données.
// Toutes les données (users, commandes, plats)
// sont stockées dans des fichiers JSON.
// -----------------------------------------

/**
 * Lit un fichier JSON et retourne son contenu sous forme de tableau PHP.
 * Retourne un tableau vide si le fichier n'existe pas ou est invalide.
 */
function lireJSON($fichier) {
    if (!file_exists($fichier)) return [];
    $contenu = file_get_contents($fichier);
    if (!$contenu) return [];
    $data = json_decode($contenu, true);
    return $data ?? [];
}

/**
 * Écrit un tableau PHP dans un fichier JSON.
 * JSON_PRETTY_PRINT = formatage lisible
 * JSON_UNESCAPED_UNICODE = accents conservés (é, à, ç...)
 */
function ecrireJSON($fichier, $data) {
    return file_put_contents(
        $fichier,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Alias de ecrireJSON() — utilisé dans certaines pages
 * pour plus de clarté sémantique.
 */
function sauvegarderJSON($fichier, $data) {
    return ecrireJSON($fichier, $data);
}


// -----------------------------------------
// GESTION DES UTILISATEURS
// -----------------------------------------

/**
 * Génère un identifiant unique pour un nouvel utilisateur.
 * Exemple : U4F3A2B (préfixe + 6 caractères hexadécimaux)
 */
function genererID($prefixe = 'U') {
    return $prefixe . strtoupper(substr(md5(uniqid()), 0, 6));
}

/**
 * Hash un mot de passe avec bcrypt (PASSWORD_DEFAULT).
 * Le hash est stocké dans le JSON à la place du mot de passe en clair.
 * Exemple de hash : $2y$10$xyz...
 */
function hasherMotDePasse($mdp) {
    return password_hash($mdp, PASSWORD_DEFAULT);
}

/**
 * Vérifie si un mot de passe correspond à son hash bcrypt.
 * Utilisé à la connexion pour authentifier l'utilisateur.
 */
function verifierMotDePasse($mdp, $hash) {
    return password_verify($mdp, $hash);
}

/**
 * Cherche un utilisateur par son email (login) dans le fichier JSON.
 * Retourne le tableau de l'utilisateur ou null s'il n'existe pas.
 * Utilisé à la connexion et pour vérifier les doublons d'email.
 */
function trouverUtilisateur($login) {
    $data = lireJSON(JSON_USERS);
    if (empty($data['utilisateurs'])) return null;
    foreach ($data['utilisateurs'] as $user) {
        if ($user['login'] === $login) return $user;
    }
    return null;
}

/**
 * Vérifie si un email est déjà utilisé par un compte existant.
 * Utilisé lors de l'inscription pour éviter les doublons.
 */
function loginExiste($login) {
    return trouverUtilisateur($login) !== null;
}

/**
 * Ajoute un nouvel utilisateur dans le fichier JSON.
 * Utilisé lors de l'inscription après validation du formulaire.
 */
function ajouterUtilisateur($nouvelUser) {
    $data = lireJSON(JSON_USERS);
    if (!isset($data['utilisateurs']) || !is_array($data['utilisateurs'])) {
        $data['utilisateurs'] = [];
    }
    $data['utilisateurs'][] = $nouvelUser;
    return ecrireJSON(JSON_USERS, $data);
}


// -----------------------------------------
// SÉCURITÉ — SESSIONS ET CONTRÔLE D'ACCÈS
// Ces fonctions protègent les pages réservées
// aux utilisateurs connectés ou à certains rôles.
// -----------------------------------------

/**
 * Vérifie si un utilisateur est connecté.
 * La session $_SESSION['user'] est créée à la connexion
 * et détruite à la déconnexion.
 */
function estConnecte() {
    return isset($_SESSION['user']);
}

/**
 * Vérifie si l'utilisateur connecté a le rôle demandé.
 * L'admin a accès à tous les rôles (accès universel).
 * Utilisé par requireRole() pour protéger les pages.
 */
function aLeRole($role) {
    if (!estConnecte()) return false;
    $userRole = $_SESSION['user']['role'];
    // L'admin peut accéder à toutes les pages protégées
    return ($userRole === $role || $userRole === 'admin');
}

/**
 * PROTECTION DE PAGE — Connexion obligatoire.
 * Redirige vers l'accueil si l'utilisateur n'est pas connecté.
 * Détecte automatiquement si la page est dans /php/ ou à la racine
 * pour construire le bon chemin de redirection.
 */
function requireConnexion() {
    if (!estConnecte()) {
        $profondeur = substr_count($_SERVER['PHP_SELF'], '/');
        $redirect = $profondeur > 2 ? '../index.php' : 'index.php';
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * PROTECTION DE PAGE — Rôle spécifique obligatoire.
 * Redirige vers l'accueil avec une erreur si l'utilisateur
 * n'a pas le rôle requis (ex: 'admin', 'restaurateur', 'livreur').
 * Utilisé en haut de chaque page réservée à un rôle précis.
 */
function requireRole($role) {
    if (!estConnecte() || !aLeRole($role)) {
        $profondeur = substr_count($_SERVER['PHP_SELF'], '/');
        $redirect = $profondeur > 2 ? '../index.php' : 'index.php';
        header('Location: ' . $redirect . '?erreur=acces_refuse');
        exit;
    }
}


// -----------------------------------------
// NETTOYAGE DES DONNÉES
// -----------------------------------------

/**
 * Nettoie une valeur reçue depuis un formulaire ($_GET / $_POST).
 * - trim() : supprime les espaces en début et fin
 * - htmlspecialchars() : convertit < > " ' & en entités HTML
 *   pour éviter les injections XSS (Cross-Site Scripting)
 */
function nettoyer($valeur) {
    return htmlspecialchars(trim($valeur), ENT_QUOTES, 'UTF-8');
}