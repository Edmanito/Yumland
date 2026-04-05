<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

            if ($userFound['role'] === 'admin') {
                header('Location: admin.php');
            } 
            elseif ($userFound['role'] === 'restaurateur') {
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

    header('Location: ../index.php?erreur=identifiants_incorrects');
    exit;
}