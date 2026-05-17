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
$bank_status  = $_GET['bank_status'] ?? '';

$api_key         = getAPIKey($vendeur);
$hash_string     = $api_key . "#" . $transaction . "#" . $montant . "#" . $vendeur . "#" . $statut . "#";
$control_calcule = md5($hash_string);

$paiement_valide = false;
$vider_panier    = false;
$message         = "Erreur de validation des données.";
$newId           = "";
$type_retour     = "inconnu"; // 'nouveau' ou 'supplement'
$type_commande      = 'livraison';
$date_planification = null;

// On valide la signature CYBank OU notre bypass ticket à 0€
if ($control_recu === $control_calcule || $bank_status === "SUCCESS_TICKETS") {

    if ($statut === "accepted" || $bank_status === "SUCCESS_TICKETS") {
        
        // -----------------------------------------------------
        // CAS A : C'EST UN SUPPLÉMENT (Préfixe S)
        // -----------------------------------------------------
        if (strpos($transaction, "S") === 0) {
            $type_retour = "supplement";
            // On récupère l'ID mémorisé en session juste avant d'aller à la banque
            $id_cmd_a_modifier = $_SESSION['cmd_supplement_id'] ?? '';

            if ($id_cmd_a_modifier) {
                $commandesData = lireJSON(JSON_COMMANDES);
                foreach ($commandesData['commandes'] as &$cmd) {
                    if ($cmd['id'] === $id_cmd_a_modifier) {
                        $cmd['paiement']['statut'] = 'paye';
                        $cmd['statut'] = 'en_attente'; // On la remet dans la file
                        break;
                    }
                }
                sauvegarderJSON(JSON_COMMANDES, $commandesData);
                $newId = $id_cmd_a_modifier;
                $paiement_valide = true;
                $message = "Supplément réglé avec succès !";
                
                // On nettoie la session
                unset($_SESSION['cmd_supplement_id']);
            } else {
                $message = "Erreur : ID de commande introuvable pour le supplément.";
            }
        } 
        // -----------------------------------------------------
        // CAS B : C'EST UNE NOUVELLE COMMANDE (Préfixe T)
        // -----------------------------------------------------
        else {
            $type_retour = "nouveau";
            
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

            $paiement_valide = true;
            $vider_panier    = true;
            $message         = "Paiement réussi ! Votre commande est en préparation.";

            $planification = $_SESSION['commande_planification'] ?? null;
            $type_commande = 'livraison';
            $date_planification = null;

            if ($planification) {
                if (!empty($planification['plan_type'])) {
                    $type_commande = $planification['plan_type'];
                }
                if (!empty($planification['plan_date']) && !empty($planification['plan_heure'])) {
                    $date_planification = $planification['plan_date'] . 'T' . $planification['plan_heure'] . ':00';
                }
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
                    'methode'          => ($bank_status === "SUCCESS_TICKETS") ? 'tickets_fidelite' : 'cybank',
                    'date_transaction' => $maintenant
                ],
                'dates'             => [
                    'commande'      => $maintenant,
                    'planification' => $date_planification  
                ]
            ];

            $commandesData['commandes'][] = $nouvelle_commande;
            sauvegarderJSON(JSON_COMMANDES, $commandesData);

            if (!empty($_SESSION['user']['tickets_reduction'])) {
                $dataUsers = lireJSON(JSON_USERS);
                foreach ($dataUsers['utilisateurs'] as &$u) {
                    if ($u['id'] === $_SESSION['user']['id']) {
                        $u['tickets_reduction'] = [];
                        $_SESSION['user'] = $u;
                        break;
                    }
                }
                sauvegarderJSON(JSON_USERS, $dataUsers);
            }

            unset($_SESSION['commande_planification']);
        }

    } else {
        $message = "Le paiement a été refusé par CYBank.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
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
        <h2 class="success">✓ <?= $type_retour === 'supplement' ? 'Supplément Réglé' : 'Commande Confirmée' ?></h2>
        <p>Référence : <strong><?= htmlspecialchars($newId) ?></strong></p>

        <?php if ($type_retour === 'nouveau'): ?>
            <?php if ($date_planification): ?>
            <div class="plan-info">
                🕐 <?= $type_commande === 'livraison' ? 'Livraison' : 'Sur place' ?>
                prévue le <?= date('d/m/Y à H:i', strtotime($date_planification)) ?>
            </div>
            <?php else: ?>
            <div class="plan-info">🍽️ Commande immédiate — en préparation</div>
            <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
        <h2 class="error">✕ Échec de la transaction</h2>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($type_retour === 'nouveau' && !empty($_SESSION['panier'])): ?>
    <div class="recap">
        <div class="recap-title">RÉCAPITULATIF</div>
        <?php foreach ($_SESSION['panier'] as $item): ?>
            <div class="ligne">
                <span><?= htmlspecialchars($item['nom'] ?? 'Produit') ?> ×<?= $item['qte'] ?></span>
                <span><?= number_format(($item['prix'] ?? 0) * $item['qte'], 2) ?>€</span>
            </div>
        <?php endforeach; ?>
        <div class="ligne total">
            <span>TOTAL PAYÉ</span>
            <span><?= number_format((float)$montant, 2) ?>€</span>
        </div>
    </div>
    <?php elseif ($type_retour === 'supplement' && $paiement_valide): ?>
    <div class="recap">
        <div class="ligne total" style="justify-content: center; color: #4caf50;">
            + <?= number_format((float)$montant, 2) ?>€
        </div>
    </div>
    <?php endif; ?>

    <?php if ($paiement_valide): ?>
        <a href="<?= $type_retour === 'supplement' ? 'profil.php' : '../index.php' ?>" class="btn">
            <?= $type_retour === 'supplement' ? 'RETOUR AU PROFIL' : 'RETOUR ACCUEIL' ?>
        </a>
    <?php else: ?>
        <a href="<?= $type_retour === 'supplement' ? 'profil.php' : 'panier.php' ?>" class="btn">RETOUR</a>
    <?php endif; ?>

</div>
<script src="../js/theme.js"></script>
</body>
</html>
<?php
if ($vider_panier) {
    $_SESSION['panier'] = [];
}
?>