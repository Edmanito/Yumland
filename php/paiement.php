<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

requireConnexion();

// 1. DÉTECTION DU CONTEXTE (Nouveau ou Supplément)
$id_commande_modif = $_GET['cmd'] ?? null;
$montant_supplement = $_GET['montant'] ?? null;

$totalFinal = 0;
$idTransaction = "";

if ($id_commande_modif && $montant_supplement) {
    // ==========================================
    // CAS A : PAIEMENT D'UN SUPPLÉMENT
    // ==========================================
    $totalFinal = (float)$montant_supplement;
    // On génère un ID court valide pour CYBank (Ex: S1714659578123)
    $idTransaction = "S" . time() . rand(100, 999); 
    // On mémorise l'ID de la commande dans la session du serveur (plus sûr)
    $_SESSION['cmd_supplement_id'] = $id_commande_modif;
} 
else {
    // ==========================================
    // CAS B : NOUVELLE COMMANDE (PANIER)
    // ==========================================
    if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
        header('Location: panier.php');
        exit;
    }

    $totalPanier = 0;
    $plats_data = lireJSON(JSON_PLATS);
    $menus_data = lireJSON(JSON_MENUS);
    $catalogue = array_merge($plats_data['plats'], $menus_data['menus']);

    foreach ($_SESSION['panier'] as $item) {
        foreach ($catalogue as $p) {
            if ($p['id'] == $item['id']) {
                $prix = $p['prix'] ?? $p['prix_total'] ?? 0;
                $totalPanier += ($prix * $item['qte']);
                break;
            }
        }
    }

    // Déduction des tickets de réduction
    $totalTickets = 0;
    if (!empty($_SESSION['user']['tickets_reduction'])) {
        foreach ($_SESSION['user']['tickets_reduction'] as $ticket) {
            $totalTickets += $ticket['montant'];
        }
    }

    $totalFinal = max(0, $totalPanier - $totalTickets);
    // On génère un ID court classique pour une nouvelle commande (Ex: T1714659578123)
    $idTransaction = "T" . time() . rand(100, 999);

    // On sauvegarde les infos de planification en session
    $_SESSION['commande_planification'] = [
        'plan_type'  => $_POST['plan_type'] ?? 'livraison',
        'plan_date'  => $_POST['plan_date'] ?? '',
        'plan_heure' => $_POST['plan_heure'] ?? '',
        'tickets_utilises' => ($totalTickets > 0)
    ];
}

// 2. PRÉPARATION CYBANK
$vendeur = "MI-1_A"; 
$montant_format = number_format($totalFinal, 2, '.', ''); 

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$url_retour = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/retour_paiement.php";

// Gestion du paiement à 0€ (Bypass banque)
if ($totalFinal == 0 && (!isset($id_commande_modif) || !$id_commande_modif)) {
    header("Location: retour_paiement.php?transaction=$idTransaction&montant=0.00&status=accepted&bank_status=SUCCESS_TICKETS");
    exit;
}

$key = getAPIKey($vendeur);
$sep = "#";
$chaine = $key . $sep . $idTransaction . $sep . $montant_format . $sep . $vendeur . $sep . $url_retour . $sep;
$control = md5($chaine);
?>

<!DOCTYPE html>
<html>
<head>
    <script>
        // Appliquer le thème AVANT le rendu pour éviter le flash
        (function(){
            const m = document.cookie.match(/(?:^|; )kaiseki_theme=([^;]*)/);
         const t = m ? decodeURIComponent(m[1]) : 'sombre';
            if (t === 'clair') {
             const l = document.createElement('link');
             l.rel = 'stylesheet'; l.id = 'theme-stylesheet';
             l.href = '../css/theme-clair.css';
                document.head.appendChild(l);
            }
        })();
    </script>
    <meta charset="UTF-8">
    <title>Paiement CYBank | Kaiseki Shunei</title>
</head>
<body onload="document.getElementById('form_pay').submit();" style="background:#000; color:#bc9c64; text-align:center; padding-top:100px; font-family:sans-serif;">
    
    <div style="border: 1px solid #bc9c64; display: inline-block; padding: 40px; background: #0a0a0a;">
        <h2 style="letter-spacing: 2px;">VÉRIFICATION ET REDIRECTION</h2>
        <p style="color: #888;">Préparation de la transaction sécurisée...</p>
        <p style="font-size: 1.2rem; margin-top: 20px;">Montant : <?= $montant_format ?>€</p>
    </div>

    <form id="form_pay" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?= $idTransaction ?>">
        <input type="hidden" name="montant"     value="<?= $montant_format ?>">
        <input type="hidden" name="vendeur"     value="<?= $vendeur ?>">
        <input type="hidden" name="retour"      value="<?= $url_retour ?>">
        <input type="hidden" name="control"     value="<?= $control ?>">
    </form>
<script src="../js/theme.js"></script>
</body>
</html>