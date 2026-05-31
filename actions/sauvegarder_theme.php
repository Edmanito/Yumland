<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Vérification du statut de l'utilisateur en session
if (isset($_SESSION['user'])) {
    $allUsersData = lireJSON(JSON_USERS);
    $currentUserFromDB = null;
    foreach ($allUsersData['utilisateurs'] as $u) {
        if ($u['id'] === $_SESSION['user']['id']) {
            $currentUserFromDB = $u;
            break;
        }
    }

    if ($currentUserFromDB && $currentUserFromDB['statut'] === 'suspendu') {
        session_destroy();
        echo json_encode(['success' => false, 'message' => 'Votre compte a été suspendu.']);
        exit;
    }
    $_SESSION['user'] = $currentUserFromDB; // Rafraîchir la session avec les dernières données
}

$theme = $_POST['theme'] ?? '';
if (!in_array($theme, ['clair', 'sombre'])) {
    http_response_code(400);
    exit;
}

if (estConnecte()) {
    $data = lireJSON(JSON_USERS);
    foreach ($data['utilisateurs'] as &$u) {
        if ($u['id'] === $_SESSION['user']['id']) {
            $u['preferences']['theme'] = $theme;
            break;
        }
    }
    ecrireJSON(JSON_USERS, $data);
    $_SESSION['user']['preferences']['theme'] = $theme;
}

http_response_code(200);
echo 'ok';
exit;