<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

// Sécurité : on définit la fonction si elle n'existe pas dans fonctions.php
if (!function_exists('sauvegarderJSON')) {
    function sauvegarderJSON($chemin, $data) {
        $json_formatte = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($chemin, $json_formatte);
    }
}

// 1. Récupération des paramètres
$transaction  = isset($_GET['transaction']) ? $_GET['transaction'] : "";
$montant      = isset($_GET['montant'])     ? $_GET['montant']     : "";
$vendeur      = isset($_GET['vendeur'])     ? $_GET['vendeur']     : "";
$statut       = isset($_GET['status'])      ? $_GET['status']      : "";
$control_recu = isset($_GET['control'])     ? $_GET['control']     : "";

// 2. Vérification de sécurité 
$api_key = getAPIKey($vendeur);
$hash_string = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";
$control_calcule = md5($hash_string);

$paiement_valide = false;
$message = "Erreur de validation.";

// 3. Comparaison et validation
if ($control_recu === $control_calcule) {
    if ($statut === "accepted") {
        $paiement_valide = true;
        $message = "Paiement réussi ! Votre commande est en préparation.";

        // --- SAUVEGARDE AU FORMAT JSON ---
        $commandesData = lireJSON('../json/commandes.json');
        if (!$commandesData) { $commandesData = array('commandes' => array()); }

        $newId = "CMD" . str_pad(count($commandesData['commandes']) + 1, 3, "0", STR_PAD_LEFT);

        $articles_formates = array();
        if (isset($_SESSION['panier'])) {
            foreach ($_SESSION['panier'] as $item) {
                $articles_formates[] = array(
                    'type' => 'plat', 
                    'id' => $item['id'],
                    'quantite' => (int)$item['qte'],
                    'prix_unitaire' => 0 
                );
            }
        }

        $nouvelle_commande = array(
            'id' => $newId,
            'id_client' => $_SESSION['user']['id'] ?? 'U999',
            'type' => 'livraison', 
            'statut' => 'en_attente',
            'adresse_livraison' => $_SESSION['user']['adresse'] ?? '',
            'etage' => '',
            'interphone' => '',
            'articles' => $articles_formates,
            'prix_total' => (float)$montant,
            'remise_appliquee' => 0,
            'paiement' => array(
                'statut' => 'paye',
                'methode' => 'cybank',
                'date_transaction' => date('Y-m-d\TH:i:s')
            ),
            'id_livreur' => null,
            'note_client' => null,
            'dates' => array(
                'commande' => date('Y-m-d\TH:i:s'),
                'preparation' => null,
                'livraison' => null
            )
        );

        $commandesData['commandes'][] = $nouvelle_commande;
        sauvegarderJSON('../json/commandes.json', $commandesData);
        $_SESSION['panier'] = array();
        
    } else {
        $message = "Le paiement a été refusé par CYBank.";
    }
} else {
    $message = "Alerte sécurité : les données ont été modifiées.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat Paiement</title>
    <style>
        body { background: #050505; color: #bc9c64; font-family: sans-serif; text-align: center; padding-top: 100px; }
        .box { border: 1px solid #bc9c64; display: inline-block; padding: 40px; border-radius: 8px; background: #111; }
        .btn { border: 1px solid #bc9c64; color: #bc9c64; text-decoration: none; padding: 12px 25px; display: inline-block; margin-top: 20px; font-weight: bold; }
        .success { color: #4BB543; }
        .error { color: #ff4d4d; }
    </style>
</head>
<body>
    <div class="box">
        <?php if ($paiement_valide): ?>
            <h2 class="success">✓ TERMINÉ</h2>
            <p>Commande <strong><?= $newId ?></strong> enregistrée.</p>
        <?php else: ?>
            <h2 class="error">✕ ÉCHEC</h2>
        <?php endif; ?>
        <p><?= $message ?></p>
        <a href="carte.php" class="btn">RETOURNER À LA BOUTIQUE</a>
    </div>
</body>
</html>