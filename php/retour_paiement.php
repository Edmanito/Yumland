<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';
require_once '../includes/getapikey.php';

// Sécurité : définition de la fonction si absente
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
$vider_panier = false;
$message = "Erreur de validation des données.";
$newId = "";

// 3. Comparaison et validation
if ($control_recu === $control_calcule) {

    // --- RÉCUPÉRATION DES INFOS PRODUITS (Pour éviter les Warnings dans tous les cas) ---
    if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
        $plats_data = lireJSON('../json/plats.json');
        $menus_data = lireJSON('../json/Menu.json');
        
        // Fusion des catalogues
        $catalogue = array_merge($plats_data['plats'] ?? [], $menus_data['menus'] ?? []);

        foreach ($_SESSION['panier'] as $key => $item) {
            foreach ($catalogue as $p) {
                if ($p['id'] == $item['id']) {
                    // On injecte le nom et le prix dans la session pour l'affichage HTML
                    $_SESSION['panier'][$key]['nom'] = $p['nom'];
                    $_SESSION['panier'][$key]['prix'] = $p['prix'];
                }
            }
        }
    }

    if ($statut === "accepted") {
        $paiement_valide = true;
        $vider_panier = true; 
        $message = "Paiement réussi ! Votre commande est en préparation.";

        // --- SAUVEGARDE AU FORMAT JSON ---
        $commandesData = lireJSON('../json/commandes.json');
        if (!$commandesData) { $commandesData = array('commandes' => array()); }

        $newId = "CMD" . str_pad(count($commandesData['commandes']) + 1, 3, "0", STR_PAD_LEFT);

        $articles_formates = array();
        foreach ($_SESSION['panier'] as $item) {
            $articles_formates[] = array(
                'type' => 'plat', 
                'id' => $item['id'],
                'nom' => $item['nom'] ?? 'Produit',
                'quantite' => (int)$item['qte'],
                'prix_unitaire' => (float)($item['prix'] ?? 0)
            );
        }

        $nouvelle_commande = array(
            'id' => $newId,
            'id_client' => $_SESSION['user']['id'] ?? 'U999',
            'type' => 'livraison', 
            'statut' => 'en_attente',
            'adresse_livraison' => $_SESSION['user']['adresse'] ?? '',
            'articles' => $articles_formates,
            'prix_total' => (float)$montant,
            'paiement' => array(
                'statut' => 'paye',
                'methode' => 'cybank',
                'date_transaction' => date('Y-m-d\TH:i:s')
            ),
            'dates' => array(
                'commande' => date('Y-m-d\TH:i:s')
            )
        );

        $commandesData['commandes'][] = $nouvelle_commande;
        sauvegarderJSON('../json/commandes.json', $commandesData);
        
    } else {
        $message = "Le paiement a été refusé par CYBank. Veuillez vérifier vos fonds.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Kaiseki Shunei - Résultat du paiement</title>
    <style>
        body { background: #050505; color: #bc9c64; font-family: sans-serif; text-align: center; padding-top: 50px; }
        .box { border: 1px solid #bc9c64; display: inline-block; padding: 40px; border-radius: 4px; background: #0f0f0f; max-width: 450px; width: 90%; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
        h2 { letter-spacing: 3px; margin-bottom: 20px; text-transform: uppercase; }
        .success { color: #4BB543; }
        .error { color: #ff4d4d; }
        
        .recap { margin: 30px 0; border-top: 1px solid #333; padding-top: 20px; text-align: left; }
        .recap-title { font-size: 0.8rem; letter-spacing: 2px; text-align: center; margin-bottom: 15px; color: #888; }
        .ligne { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; }
        .total { border-top: 1px solid #bc9c64; margin-top: 10px; padding-top: 10px; font-weight: bold; font-size: 1.1rem; color: #bc9c64; }
        
        .btn { border: 1px solid #bc9c64; color: #bc9c64; text-decoration: none; padding: 12px 25px; display: inline-block; margin-top: 20px; font-size: 0.8rem; letter-spacing: 2px; font-weight: bold; transition: 0.3s; }
        .btn:hover { background: #bc9c64; color: #000; }
    </style>
</head>
<body>
    <div class="box">
        <?php if ($paiement_valide): ?>
            <h2 class="success">✓ Accepté</h2>
            <p>Commande <strong><?= $newId ?></strong> enregistrée.</p>
        <?php else: ?>
            <h2 class="error">✕ Échec</h2>
            <p><?= $message ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])): ?>
        <div class="recap">
            <div class="recap-title">DÉTAILS DE LA COMMANDE</div>
            <?php foreach ($_SESSION['panier'] as $item): ?>
                <div class="ligne">
                    <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?> x<?= $item['qte'] ?></span>
                    <span><?= number_format(($item['prix'] ?? 0) * $item['qte'], 2) ?> €</span>
                </div>
            <?php endforeach; ?>
            <div class="ligne total">
                <span>TOTAL</span>
                <span><?= number_format((float)$montant, 2) ?> €</span>
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
    $_SESSION['panier'] = array();
}
?>