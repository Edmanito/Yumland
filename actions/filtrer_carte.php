<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Désactiver l'affichage des erreurs HTML pour garantir un JSON propre
error_reporting(0);
header('Content-Type: application/json');

// Récupération des filtres depuis l'URL
$regime = $_GET['regime'] ?? 'tous';
$saveur = $_GET['saveur'] ?? 'tous';
$recherche = trim($_GET['recherche'] ?? '');

// Lecture des plats depuis ton config.php (qui est parfait)
$dataPlats = lireJSON(JSON_PLATS);
$plats = $dataPlats['plats'] ?? [];

$resultats = array_filter($plats, function($p) use ($regime, $saveur, $recherche) {
    $match = true;

    // Filtre Régime
    if ($regime !== 'tous' && (empty($p['regimes']) || !in_array($regime, (array)$p['regimes']))) {
        $match = false;
    }

    // Filtre Saveur
    if ($saveur !== 'tous' && (empty($p['saveurs']) || !in_array($saveur, (array)$p['saveurs']))) {
        $match = false;
    }

    // Recherche textuelle (stripos est compatible avec toutes les versions de PHP)
    if (!empty($recherche)) {
        $nom = $p['nom'] ?? '';
        $desc = $p['description'] ?? '';
        if (stripos($nom, $recherche) === false && stripos($desc, $recherche) === false) {
            $match = false;
        }
    }

    return $match;
});

// Renvoie le tableau filtré
echo json_encode(array_values($resultats));
exit;