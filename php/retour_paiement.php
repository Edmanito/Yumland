<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

date_default_timezone_set('Europe/Paris');

if (!function_exists('sauvegarderJSON')) {
    function sauvegarderJSON($chemin, $data) {
        return file_put_contents($chemin, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

$transaction  = $_GET['transaction'] ?? '';
$montant      = $_GET['montant']     ?? '';
$vendeur      = $_GET['vendeur']     ?? '';
$statut       = $_GET['status']      ?? '';
$control_recu = $_GET['control']     ?? '';

$api_key         = getAPIKey($vendeur);
$hash_string     = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";
$control_calcule = md5($hash_string);

$paiement_valide = false;
$vider_panier    = false;
$message         = "Erreur de validation des données.";
$newId           = "";

if ($control_recu === $control_calcule) {

    if (!empty($_SESSION['panier'])) {
        $plats_data = lireJSON(JSON_PLATS);
        $menus_data = lireJSON(JSON_MENUS);
        $catalogue  = array_merge($plats_data['plats'] ?? [], $menus_data['menus'] ?? []);

        foreach ($_SESSION['panier'] as $key => $item) {
            foreach ($catalogue as $p) {
                if ($p['id'] == $item['id']) {
                    $_SESSION['panier'][$key]['nom']  = $p['nom'];
                    $_SESSION['panier'][$key]['prix'] = $p['prix'] ?? $p['prix_total'] ?? 0;
                }
            }
        }
    }

    if ($statut === "accepted") {
        $paiement_valide = true;
        $vider_panier    = true;
        $message         = "Paiement réussi ! Votre commande est en préparation.";

        $planification = $_SESSION['planification'] ?? null;

        $type_commande = 'livraison';
        if ($planification) {
            $type_commande = $planification['type'] ?? 'livraison';
        }

        $date_planification = null;
        if ($planification && !empty($planification['date']) && !empty($planification['heure'])) {
            $date_planification = $planification['date'] . 'T' . $planification['heure'] . ':00';
        }

        $maintenant = date('Y-m-d\TH:i:s');

        $articles_formates = [];
        foreach ($_SESSION['panier'] as $item) {
            $articles_formates[] = [
                'type'         => 'plat',
                'id'           => $item['id'],
                'nom'          => $item['nom']  ?? 'Produit',
                'quantite'     => (int)$item['qte'],
                'prix_unitaire'=> (float)($item['prix'] ?? 0)
            ];
        }

        
        $adresse = $_SESSION['user']['infos']['adresse'] ?? '';

        $commandesData = lireJSON(JSON_COMMANDES);
        if (!$commandesData) { $commandesData = ['commandes' => []]; }

        $newId = "CMD" . str_pad(count($commandesData['commandes']) + 1, 3, "0", STR_PAD_LEFT);

        $nouvelle_commande = [
            'id'                => $newId,
            'id_client'         => $_SESSION['user']['id'] ?? 'U999',
            'type'              => $type_commande,
            'statut'            => 'en_attente',
            'adresse_livraison' => $adresse,
            'articles'          => $articles_formates,
            'prix_total'        => (float)$montant,
            'paiement'          => [
                'statut'           => 'paye',
                'methode'          => 'cybank',
                'date_transaction' => $maintenant
            ],
            'dates'             => [
                'commande'      => $maintenant,
                'planification' => $date_planification  
            ]
        ];

        $commandesData['commandes'][] = $nouvelle_commande;
        sauvegarderJSON(JSON_COMMANDES, $commandesData);

        unset($_SESSION['planification']);

    } else {
        $message = "Le paiement a été refusé par CYBank.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Kaiseki Shunei — Résultat du paiement</title>
    <style>
        body { background: #050505; color: #bc9c64; font-family: sans-serif; text-align: center; padding-top: 50px; }
        .box { border: 1px solid #bc9c64; display: inline-block; padding: 40px; background: #0f0f0f; max-width: 480px; width: 90%; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
        h2 { letter-spacing: 3px; margin-bottom: 20px; text-transform: uppercase; }
        .success { color: #4BB543; }
        .error   { color: #ff4d4d; }
        .plan-info { background: rgba(188,156,100,0.08); border: 1px solid rgba(188,156,100,0.2); padding: 12px 16px; margin: 16px 0; font-size: 0.82rem; letter-spacing: 1px; }
        .recap { margin: 24px 0; border-top: 1px solid #333; padding-top: 20px; text-align: left; }
        .recap-title { font-size: 0.75rem; letter-spacing: 2px; text-align: center; margin-bottom: 15px; color: #888; }
        .ligne { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.88rem; }
        .total { border-top: 1px solid #bc9c64; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 1.1rem; }
        .btn { border: 1px solid #bc9c64; color: #bc9c64; text-decoration: none; padding: 12px 28px; display: inline-block; margin-top: 24px; font-size: 0.75rem; letter-spacing: 2px; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #bc9c64; color: #000; }
    </style>
</head>
<body>
<div class="box">

    <?php if ($paiement_valide): ?>
        <h2 class="success">✓ Commande confirmée</h2>
        <p>Référence : <strong><?= $newId ?></strong></p>

        <?php if ($date_planification): ?>
        <div class="plan-info">
            🕐 <?= $type_commande === 'livraison' ? 'Livraison' : 'Sur place' ?>
            prévue le <?= date('d/m/Y à H:i', strtotime($date_planification)) ?>
        </div>
        <?php else: ?>
        <div class="plan-info">🍽️ Commande immédiate — en préparation</div>
        <?php endif; ?>

    <?php else: ?>
        <h2 class="error">✕ Paiement échoué</h2>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['panier'])): ?>
    <div class="recap">
        <div class="recap-title">RÉCAPITULATIF</div>
        <?php foreach ($_SESSION['panier'] as $item): ?>
            <div class="ligne">
                <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?> ×<?= $item['qte'] ?></span>
                <span><?= number_format(($item['prix'] ?? 0) * $item['qte'], 2) ?>€</span>
            </div>
        <?php endforeach; ?>
        <div class="ligne total">
            <span>TOTAL</span>
            <span><?= number_format((float)$montant, 2) ?>€</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($paiement_valide): ?>
        <a href="../index.php" class="btn">RETOUR ACCUEIL</a>
    <?php else: ?>
        <a href="panier.php" class="btn">MODIFIER LE PANIER</a>
    <?php endif; ?>

</div>
</body>
</html>
<?php
if ($vider_panier) {
    $_SESSION['panier'] = [];
}
?>