<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

$_SESSION['panier'] = [];

header('Location: panier.php');
exit;