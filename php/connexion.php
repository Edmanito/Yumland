<?php
// On remonte d'un cran pour atteindre les includes
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// On démarre la session si ce n'est pas déjà fait par config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = nettoyer($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userFound = trouverUtilisateur($email);

    if ($userFound) {
        // Vérification hybride : Hash (production) OU Texte brut (pour Kenji/tests)
        $match = password_verify($password, $userFound['mot_de_passe']) || ($password === $userFound['mot_de_passe']);

        if ($match) {
            if ($userFound['statut'] === 'suspendu') {
                header('Location: ../index.php?erreur=compte_suspendu');
                exit;
            }

            $_SESSION['user'] = $userFound;

            // --- NAVIGATION SELON LE RÔLE ---
            if ($userFound['role'] === 'admin') {
                header('Location: admin.php');
            } 
            elseif ($userFound['role'] === 'restaurateur') {
                // Direction le fichier commande.php dans le dossier php/
                header('Location: commande.php'); 
            } 
            elseif ($userFound['role'] === 'livreur') {
                header('Location: livraison.php'); 
            } 
            else {
                header('Location: ../index.php');
            }
            exit;
        }
    }

    // Si on arrive ici, c'est que les identifiants sont faux
    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}