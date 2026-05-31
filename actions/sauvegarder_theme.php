<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

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