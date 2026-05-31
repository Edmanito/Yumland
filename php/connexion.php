<?php
// =========================================
// KAISEKI SHUNEI — PHP/CONNEXION.PHP
// =========================================

require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Démarre la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Nettoyage de l'email reçu du formulaire (anti-XSS)
    $email    = nettoyer($_POST['email'] ?? '');
    // Le mot de passe n'est pas nettoyé pour ne pas altérer les caractères spéciaux
    $password = $_POST['password'] ?? '';

    // Recherche du compte par email dans le JSON utilisateurs
    $userFound = trouverUtilisateur($email);

    if ($userFound) {
        // Vérification du mot de passe :
        // password_verify() pour les comptes hashés (bcrypt)
        // comparaison directe pour les comptes de démonstration en clair
        $match = password_verify($password, $userFound['mot_de_passe'])
              || ($password === $userFound['mot_de_passe']);

        if ($match) {
            // Vérification que le compte n'est pas suspendu par l'admin
            if ($userFound['statut'] === 'suspendu') {
                header('Location: ../index.php?erreur=compte_suspendu');
                exit;
            }

            // Création de la session utilisateur
            // Le mot de passe n'est PAS stocké en session (sécurité)
            $_SESSION['user'] = $userFound;

            // -----------------------------------------
            // RESTAURATION DU THÈME DU COMPTE
            // À la connexion, on relit le thème         
            // -----------------------------------------
            $themeCompte = $userFound['preferences']['theme'] ?? 'sombre';
            setcookie(
                'kaiseki_theme',        // Nom du cookie
                $themeCompte,           // Valeur : 'clair' ou 'sombre'
                time() + 365*24*3600,   // Expiration : 1 an
                '/',                    // Accessible sur tout le site
                '',                     // Domaine (vide = domaine actuel)
                false,                  // Secure (false car HTTP en local)
                false                   // HttpOnly (false car lu en JS)
            );

            // Redirection selon le rôle de l'utilisateur
            if ($userFound['role'] === 'admin') {
                header('Location: admin.php');
            } elseif ($userFound['role'] === 'restaurateur') {
                header('Location: commande.php');
            } elseif ($userFound['role'] === 'livreur') {
                header('Location: livraison.php');
            } else {
                // Client standard : retour à l'accueil
                header('Location: ../index.php');
            }
            exit;
        }
    }

    // En cas d'échec : on mémorise l'email en session pour
    // le pré-remplir dans le formulaire (meilleure UX)
    $_SESSION['tentative_email'] = $email;
    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}