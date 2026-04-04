<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

// 1. Calcul du montant total
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
                $total = $total + ($p['prix'] * $quantite);
            }
        }
    }
}

// 2. Paramètres CYBank (Citations PDF )
$vendeur = "MI-1_A"; // Remplace par ton groupe si besoin 
$transaction = "T" . time() . rand(100, 999); 
$montant = number_format($total, 2, '.', ''); 

// URL de retour absolue (Ajuste le port si nécessaire)
$url_retour = "http://localhost:7070/php/retour_paiement.php";

// 3. Calcul de la sécurité (Control)
$key = getAPIKey($vendeur);
$sep = "#";

// Construction manuelle pour éviter toute erreur de token
$chaine = $key;
$chaine .= $sep;
$chaine .= $transaction;
$chaine .= $sep;
$chaine .= $montant;
$chaine .= $sep;
$chaine .= $vendeur;
$chaine .= $sep;
$chaine .= $url_retour;
$chaine .= $sep;

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