<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// On indique qu'on répond en format JSON (très important pour l'AJAX)
header('Content-Type: application/json');

if (!estConnecte()) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit;
}

// 1. Récupération des données envoyées par Fetch
$email = trim($_POST['login'] ?? '');
$mdp = trim($_POST['mdp'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$etage = trim($_POST['etage'] ?? '');
$interphone = trim($_POST['interphone'] ?? '');

// 2. Vérification côté serveur
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
    exit;
}

$dataUsers = lireJSON(JSON_USERS);
$userId = $_SESSION['user']['id'];
$userIndex = null;

// 3. Trouver l'utilisateur et vérifier si l'email n'est pas déjà pris
foreach ($dataUsers['utilisateurs'] as $index => $u) {
    if ($u['login'] === $email && $u['id'] !== $userId) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre compte.']);
        exit;
    }
    if ($u['id'] === $userId) {
        $userIndex = $index;
    }
}

if ($userIndex !== null) {
    // 4. Mise à jour des données (dont les nouvelles adresses)
    $dataUsers['utilisateurs'][$userIndex]['login'] = $email;
    $dataUsers['utilisateurs'][$userIndex]['infos']['telephone'] = $telephone;
    $dataUsers['utilisateurs'][$userIndex]['infos']['adresse'] = $adresse;
    $dataUsers['utilisateurs'][$userIndex]['infos']['etage'] = $etage;
    $dataUsers['utilisateurs'][$userIndex]['infos']['interphone'] = $interphone;
    
    if (!empty($mdp)) {
        // On ne modifie le mot de passe que si le champ n'est pas vide
        $dataUsers['utilisateurs'][$userIndex]['mdp'] = password_hash($mdp, PASSWORD_DEFAULT);
    }

    // 5. Sauvegarde dans le fichier JSON
    ecrireJSON(JSON_USERS, $dataUsers);
    
    // 6. Mise à jour de la session pour que l'affichage reste à jour
    $_SESSION['user'] = $dataUsers['utilisateurs'][$userIndex];

    // 7. On renvoie un succès au JavaScript
    echo json_encode([
        'success' => true, 
        'message' => 'Profil mis à jour'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
}