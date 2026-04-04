<?php
// =========================================
// KAISEKI SHUNEI — CONFIG.PHP
// =========================================

define('JSON_DIR',       __DIR__ . '/../json/');
define('JSON_USERS',     JSON_DIR . 'utilisateurs.json');
define('JSON_PLATS',     JSON_DIR . 'plats.json');
define('JSON_MENUS',     JSON_DIR . 'menus.json');
define('JSON_COMMANDES', JSON_DIR . 'commandes.json');

define('SITE_NOM', 'Kaiseki Shunei');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}