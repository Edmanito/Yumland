<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

// Force le fuseau horaire pour que le calcul temporel corresponde à ton ordinateur
date_default_timezone_set('Europe/Paris');

requireRole('restaurateur');

// 1. CHARGEMENT DES DONNÉES
$dataCommandes = lireJSON(JSON_COMMANDES);
$toutes = $dataCommandes['commandes'] ?? [];

// On charge les plats pour transformer les IDs en Noms
$dataPlats = lireJSON('../json/plats.json');
$platsMap = [];
if (isset($dataPlats['plats'])) {
    foreach ($dataPlats['plats'] as $p) {
        $platsMap[$p['id']] = $p['nom'];
    }
}

// 2. LOGIQUE DE FILTRE TEMPOREL
$maintenant = time();
$marge_preparation = 30 * 60; // 30 minutes de marge

$attente = array_values(array_filter($toutes, function($c) use ($maintenant, $marge_preparation) {
    if ($c['statut'] !== 'en_attente') return false;
    
    if (!empty($c['dates']['planification'])) {
        $heure_voulue = strtotime($c['dates']['planification']);
        return $maintenant >= ($heure_voulue - $marge_preparation);
    }
    
    return true; 
}));

$cuisine    = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_preparation'));
$pret       = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'pret'));
$livraison  = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_livraison'));

// 3. RÉCUPÉRATION DES LIVREURS
$dataUsers = lireJSON(JSON_USERS);
$livreursActifs = array_filter($dataUsers['utilisateurs'] ?? [], fn($u) => $u['role'] === 'livreur' && $u['statut'] === 'actif');

// 4. FONCTION D'AFFICHAGE DES CARTES
function afficherCartes($commandes, $btnLabel, $btnClass, $nextStatut, $livreurs = [], $platsMap = []) {
    foreach ($commandes as $cmd): ?>
    <article class="order-card">
        <div class="card-top">
            <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
            <div class="time-block" style="text-align: right;">
                <span class="order-date" style="display: block; font-size: 0.7rem; opacity: 0.6;">
                    <?= date('d/m', strtotime($cmd['dates']['commande'])) ?>
                </span>
                <span class="order-time" style="font-weight: bold;">
                    <?= date('H:i', strtotime($cmd['dates']['commande'])) ?>
                </span>
                <?php if (!empty($cmd['dates']['planification'])): ?>
                    <span class="plan-time" style="color: #ffcc00; font-size: 0.7rem; display: block; margin-top: 2px;">
                        📅 <?= date('H:i', strtotime($cmd['dates']['planification'])) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-client" style="display: flex; justify-content: space-between; align-items: center; margin: 10px 0;">
            <span class="client-name" style="font-weight: 700;"><?= htmlspecialchars($cmd['id_client']) ?></span>
            <span class="order-type <?= $cmd['type'] ?>">
                <?= $cmd['type'] === 'livraison' ? '🏠 LIVRAISON' : '🍽️ SUR PLACE' ?>
            </span>
        </div>

        <div class="card-items">
            <?php foreach ($cmd['articles'] as $art): ?>
            <div class="item-line" style="display: flex; font-size: 0.9rem; margin-bottom: 4px;">
                <span class="item-qty" style="color: #bc9c64; margin-right: 8px;"><?= $art['quantite'] ?>×</span>
                <span class="item-name"><?= htmlspecialchars($platsMap[$art['id']] ?? $art['id']) ?></span>
                <span style="margin-left:auto; opacity: 0.6;"><?= $art['prix_unitaire'] * $art['quantite'] ?>€</span>
            </div>
            <?php endforeach; ?>
            
            <div class="card-footer-info" style="border-top: 1px solid #222; margin-top: 10px; padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                <div class="total-line" style="font-weight: 700; color: #bc9c64;">Total : <?= $cmd['prix_total'] ?>€</div>
                <div class="payment-tag <?= $cmd['paiement']['statut'] ?>">
                    <?= $cmd['paiement']['statut'] === 'paye' ? 'PAYÉ' : 'À ENCAISSER' ?>
                </div>
            </div>
        </div>

        <div class="card-actions" style="margin-top: 15px;">
            <?php if ($cmd['statut'] === 'pret' && $cmd['type'] === 'livraison'): ?>
                <form action="../actions/assigner_livreur.php" method="POST" style="width:100%;">
                    <input type="hidden" name="id_commande" value="<?= $cmd['id'] ?>">
                    <select name="id_livreur" required style="width:100%; margin-bottom:8px; padding:8px; background:#111; color:#fff; border:1px solid #333; border-radius:4px;">
                        <option value="">-- Choisir Livreur --</option>
                        <?php foreach ($livreurs as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['infos']['prenom'] ?? $l['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-action <?= $btnClass ?>" style="width:100%; border:none; cursor:pointer; padding: 10px; border-radius: 4px; font-weight: bold;">
                        <?= $btnLabel ?>
                    </button>
                </form>
            <?php else: ?>
                <a href="../actions/statut_commande.php?id=<?= $cmd['id'] ?>&statut=<?= $nextStatut ?>" class="btn-action <?= $btnClass ?>" style="display: block; text-align: center; text-decoration: none; padding: 10px; border-radius: 4px; font-weight: bold;">
                    <?= $btnLabel ?>
                </a>
            <?php endif; ?>
        </div>
    </article>
    <?php endforeach;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuisine | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/commande.css">
</head>
<body class="page-commande-admin">

    <nav class="topbar">
        <div class="topbar-left">
            <a href="../index.php" class="btn-back">← Accueil</a>
            <div class="brand">
                <span class="brand-kanji">春栄</span>
                <span class="brand-name">Kaiseki Shunei</span>
            </div>
        </div>
        <div class="topbar-center">
            <span class="live-dot"></span>
            <span class="live-label">CUISINE EN DIRECT</span>
        </div>
        <div class="topbar-right">
            <div class="clock" id="clock">--:--</div>
        </div>
    </nav>

    <div class="page-header">
        <div class="stats-row">
            <div class="stat-pill"><span class="stat-num"><?= count($attente) ?></span><span class="stat-txt">En attente</span></div>
            <div class="stat-pill accent"><span class="stat-num"><?= count($cuisine) ?></span><span class="stat-txt">En cuisine</span></div>
            <div class="stat-pill green"><span class="stat-num"><?= count($pret) ?></span><span class="stat-txt">Prêtes</span></div>
            <div class="stat-pill muted"><span class="stat-num"><?= count($livraison) ?></span><span class="stat-txt">En livraison</span></div>
        </div>
    </div>

    <main class="kanban">
        <div class="kanban-col">
            <div class="col-header waiting"><h2>En attente</h2><span class="col-count"><?= count($attente) ?></span></div>
            <div class="cards-list">
                <?php afficherCartes($attente, 'Prendre en charge', 'primary', 'en_preparation', [], $platsMap); ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header cooking"><h2>En cuisine</h2><span class="col-count"><?= count($cuisine) ?></span></div>
            <div class="cards-list">
                <?php afficherCartes($cuisine, 'Marquer prêt', 'success', 'pret', [], $platsMap); ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header ready"><h2>Prêtes</h2><span class="col-count"><?= count($pret) ?></span></div>
            <div class="cards-list">
                <?php afficherCartes($pret, 'Confier & Livrer', 'gold', 'en_livraison', $livreursActifs, $platsMap); ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header delivering"><h2>En livraison</h2><span class="col-count"><?= count($livraison) ?></span></div>
            <div class="cards-list">
                <?php foreach ($livraison as $cmd): ?>
                <article class="order-card">
                    <div class="card-top">
                        <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
                        <div class="time-block" style="text-align: right;">
                             <span class="order-date" style="display: block; font-size: 0.7rem; opacity: 0.6;">
                                <?= date('d/m', strtotime($cmd['dates']['commande'])) ?>
                            </span>
                            <span class="order-time" style="font-weight: bold;">
                                <?= date('H:i', strtotime($cmd['dates']['commande'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-client">
                        <span class="client-name"><?= htmlspecialchars($cmd['id_client']) ?></span>
                        <span class="order-type livraison">🏠 LIVRAISON</span>
                    </div>
                    <div class="card-items">
                        <div class="livreur-info" style="display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.05); padding: 8px; border-radius: 4px;">
                            <span class="livreur-icon">🛵</span>
                            <span style="font-size: 0.85rem;"><?= htmlspecialchars($cmd['id_livreur'] ?? 'Non assigné') ?></span>
                        </div>
                        <div class="payment-tag <?= $cmd['paiement']['statut'] ?>" style="margin-top: 10px; display: inline-block;">
                            <?= $cmd['paiement']['statut'] === 'paye' ? 'PAYÉ' : 'À ENCAISSER' ?>
                        </div>
                    </div>
                    <div class="card-actions"><button class="btn-action muted" disabled style="width: 100%; opacity: 0.5;">En route...</button></div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.getHours().toString().padStart(2, '0') + ":" + now.getMinutes().toString().padStart(2, '0');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>