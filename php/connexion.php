<?php
// On remonte d'un cran pour atteindre les includes
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = nettoyer($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userFound = trouverUtilisateur($email);

    if ($userFound) {
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
                header('Location: ../restaurateur/dashboard.php');
            } 
            elseif ($userFound['role'] === 'livreur') {
                // MODIFICATION ICI : On pointe vers le bon fichier
                // Si livraison.php est dans le même dossier que connexion.php :
                header('Location: livraison.php'); 
            } 
            else {
                header('Location: ../index.php');
            }
            exit;
        }
    }

    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}