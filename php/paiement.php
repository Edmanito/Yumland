<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

$total = 0;
if (isset($_SESSION['panier'])) {
    $plats_data = lireJSON(JSON_PLATS);
    $menus_data = lireJSON(JSON_MENUS);
    $catalogue = array_merge($plats_data['plats'], $menus_data['menus']);

    foreach ($_SESSION['panier'] as $item) {
        $id_recherche = $item['id'];
        $quantite = $item['qte'];
        foreach ($catalogue as $p) {
            if ($p['id'] == $id_recherche) {
                $prix = $p['prix'] ?? $p['prix_total'] ?? 0;
                $total = $total + ($prix * $quantite);
            }
        }
    }
}

$vendeur = "MI-1_A"; 
$transaction = "T" . time() . rand(100, 999); 
$montant = number_format($total, 2, '.', ''); 


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST']; 

$url_retour = $protocol . "://" . $host . "/php/retour_paiement.php";


$key = getAPIKey($vendeur);
$sep = "#";

$chaine = $key . $sep . $transaction . $sep . $montant . $sep . $vendeur . $sep . $url_retour . $sep;
$control = md5($chaine);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Paiement CYBank</title>
</head>
<body onload="document.getElementById('form_pay').submit();" style="background:#000; color:#bc9c64; text-align:center; padding-top:100px;">
    
    <h2>Redirection vers CYBank...</h2>

    <form id="form_pay" action="https://www.plateforme-smc.fr/cybank/index.php" method="POST">
        <input type="hidden" name="transaction" value="<?php echo $transaction; ?>">
        <input type="hidden" name="montant" value="<?php echo $montant; ?>">
        <input type="hidden" name="vendeur" value="<?php echo $vendeur; ?>">
        <input type="hidden" name="retour" value="<?php echo $url_retour; ?>">
        <input type="hidden" name="control" value="<?php echo $control; ?>">
    </form>

</body>
</html>

