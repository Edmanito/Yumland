<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

date_default_timezone_set('Europe/Paris');

requireRole('restaurateur');

function dateLocale($dateStr) {
    if (empty($dateStr)) return null;
    try {
        return new DateTime($dateStr, new DateTimeZone('Europe/Paris'));
    } catch (Exception $e) {
        return null;
    }
}

function formatHeure($dateStr) {
    $dt = dateLocale($dateStr);
    return $dt ? $dt->format('H:i') : '--:--';
}

function formatDate($dateStr) {
    $dt = dateLocale($dateStr);
    return $dt ? $dt->format('d/m/Y') : '--/--';
}

function afficherTemps($cmd) {
    $plan = $cmd['dates']['planification'] ?? null;
    if (!empty($plan)) {
        echo '<span class="order-date">' . formatDate($plan) . '</span>';
        echo '<span class="order-time">' . formatHeure($plan) . '</span>';
        echo '<span class="plan-time">📅 PLANIFIÉ</span>';
    } else {
        echo '<span class="order-date">' . formatDate($cmd['dates']['commande']) . '</span>';
        echo '<span class="order-time">' . formatHeure($cmd['dates']['commande']) . '</span>';
    }
}

// 1. CHARGEMENT DES DONNÉES
$dataCommandes = lireJSON(JSON_COMMANDES);
$toutes = $dataCommandes['commandes'] ?? [];

$dataPlats = lireJSON(JSON_PLATS);
$platsMap = [];
foreach ($dataPlats['plats'] ?? [] as $p) {
    $platsMap[$p['id']] = $p['nom'];
}
$dataMenus = lireJSON(JSON_MENUS);
foreach ($dataMenus['menus'] ?? [] as $m) {
    $platsMap[$m['id']] = $m['nom'];
}

// 2. FILTRES
$attente   = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_attente'));
$cuisine   = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_preparation'));
$pret      = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'pret'));
$livraison = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_livraison'));

// 3. LIVREURS
$dataUsers      = lireJSON(JSON_USERS);
$livreursActifs = array_filter($dataUsers['utilisateurs'] ?? [], fn($u) => $u['role'] === 'livreur' && $u['statut'] === 'actif');

// 4. AFFICHAGE DES CARTES
function afficherCartes($commandes, $btnLabel, $btnClass, $nextStatut, $livreurs = [], $platsMap = []) {
    foreach ($commandes as $cmd): ?>
    <article class="order-card">

        <div class="card-top">
            <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
            <div class="time-block" style="text-align:right;">
                <?php afficherTemps($cmd); ?>
            </div>
        </div>

        <div class="card-client" style="display:flex; justify-content:space-between; align-items:center; margin:10px 0;">
            <span class="client-name" style="font-weight:700;"><?= htmlspecialchars($cmd['id_client']) ?></span>
            <span class="order-type <?= $cmd['type'] ?>">
                <?= $cmd['type'] === 'livraison' ? '🏠 LIVRAISON' : '🍽️ SUR PLACE' ?>
            </span>
        </div>

        <div class="card-items">
            <?php foreach ($cmd['articles'] as $art): ?>
            <div class="item-line">
                <span class="item-qty"><?= $art['quantite'] ?>×</span>
                <span class="item-name"><?= htmlspecialchars($platsMap[$art['id']] ?? $art['nom'] ?? $art['id']) ?></span>
                <span style="margin-left:auto; opacity:0.6;"><?= $art['prix_unitaire'] * $art['quantite'] ?>€</span>
            </div>
            <?php endforeach; ?>
            <div class="card-footer-info">
                <div class="total-line">Total : <?= $cmd['prix_total'] ?>€</div>
                <div class="payment-tag <?= $cmd['paiement']['statut'] ?>">
                    <?= $cmd['paiement']['statut'] === 'paye' ? 'PAYÉ' : 'À ENCAISSER' ?>
                </div>
            </div>
        </div>

        <div class="card-actions" style="margin-top:15px;">
            <?php if ($cmd['statut'] === 'pret' && $cmd['type'] === 'livraison'): ?>
                <form action="../actions/assigner_livreur.php" method="POST" style="width:100%;">
                    <input type="hidden" name="id_commande" value="<?= $cmd['id'] ?>">
                    <select name="id_livreur" required style="width:100%; margin-bottom:8px; padding:8px; background:#111; color:#fff; border:1px solid #333; border-radius:4px;">
                        <option value="">-- Choisir Livreur --</option>
                        <?php foreach ($livreurs as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['infos']['prenom'] ?? $l['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-action <?= $btnClass ?>" style="width:100%; border:none; cursor:pointer;">
                        <?= $btnLabel ?>
                    </button>
                </form>
            <?php else: ?>
                <a href="../actions/statut_commande.php?id=<?= $cmd['id'] ?>&statut=<?= $nextStatut ?>"
                   class="btn-action <?= $btnClass ?>"
                   style="display:block; text-align:center; text-decoration:none;">
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
            <div class="stat-pill">        <span class="stat-num"><?= count($attente) ?></span>  <span class="stat-txt">En attente</span></div>
            <div class="stat-pill accent"> <span class="stat-num"><?= count($cuisine) ?></span>  <span class="stat-txt">En cuisine</span></div>
            <div class="stat-pill green">  <span class="stat-num"><?= count($pret) ?></span>     <span class="stat-txt">Prêtes</span></div>
            <div class="stat-pill muted">  <span class="stat-num"><?= count($livraison) ?></span><span class="stat-txt">En livraison</span></div>
        </div>
    </div>

    <main class="kanban">

        <div class="kanban-col">
            <div class="col-header waiting"><h2>En attente</h2><span class="col-count"><?= count($attente) ?></span></div>
            <div class="cards-list"><?php afficherCartes($attente, 'Prendre en charge', 'primary', 'en_preparation', [], $platsMap); ?></div>
        </div>

        <div class="kanban-col">
            <div class="col-header cooking"><h2>En cuisine</h2><span class="col-count"><?= count($cuisine) ?></span></div>
            <div class="cards-list"><?php afficherCartes($cuisine, 'Marquer prêt', 'success', 'pret', [], $platsMap); ?></div>
        </div>

        <div class="kanban-col">
            <div class="col-header ready"><h2>Prêtes</h2><span class="col-count"><?= count($pret) ?></span></div>
            <div class="cards-list"><?php afficherCartes($pret, 'Confier & Livrer', 'gold', 'en_livraison', $livreursActifs, $platsMap); ?></div>
        </div>

        <div class="kanban-col">
            <div class="col-header delivering"><h2>En livraison</h2><span class="col-count"><?= count($livraison) ?></span></div>
            <div class="cards-list">
                <?php foreach ($livraison as $cmd): ?>
                <article class="order-card">
                    <div class="card-top">
                        <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
                        <div class="time-block" style="text-align:right;">
                            <?php afficherTemps($cmd); ?>
                        </div>
                    </div>
                    <div class="card-client">
                        <span class="client-name"><?= htmlspecialchars($cmd['id_client']) ?></span>
                        <span class="order-type livraison">🏠 LIVRAISON</span>
                    </div>
                    <div class="card-items">
                        <div class="livreur-info">
                            <span>🛵</span>
                            <span><?= htmlspecialchars($cmd['id_livreur'] ?? 'Non assigné') ?></span>
                        </div>
                        <div class="payment-tag <?= $cmd['paiement']['statut'] ?>" style="margin-top:10px; display:inline-block;">
                            <?= $cmd['paiement']['statut'] === 'paye' ? 'PAYÉ' : 'À ENCAISSER' ?>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-action muted" disabled style="width:100%; opacity:0.5;">En route...</button>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent =
                now.getHours().toString().padStart(2,'0') + ':' +
                now.getMinutes().toString().padStart(2,'0');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>