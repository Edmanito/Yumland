<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('admin');

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: ../php/admin.php');
    exit;
}

$data = lireJSON(JSON_USERS);
foreach ($data['utilisateurs'] as &$u) {
    if ($u['id'] === $id) {
        $u['statut'] = ($u['statut'] === 'suspendu') ? 'actif' : 'suspendu';
        break;
    }
}

ecrireJSON(JSON_USERS, $data);
header('Location: ../php/admin.php');
exit;