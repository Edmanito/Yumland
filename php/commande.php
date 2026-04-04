<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

//requireRole('restaurateur');

$dataCommandes = lireJSON(JSON_COMMANDES);
$toutes = $dataCommandes['commandes'] ?? [];

$attente    = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_attente'));
$cuisine    = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_preparation'));
$pret       = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'pret'));
$livraison  = array_values(array_filter($toutes, fn($c) => $c['statut'] === 'en_livraison'));

// Livreurs disponibles
$dataUsers = lireJSON(JSON_USERS);
$livreurs = array_filter($dataUsers['utilisateurs'], fn($u) => $u['role'] === 'livreur' && $u['statut'] === 'actif');

function afficherCartes($commandes, $btnLabel, $btnClass, $nextStatut) {
    foreach ($commandes as $cmd): ?>
    <article class="order-card <?= $cmd['statut'] === 'en_attente' ? '' : '' ?>">
        <div class="card-top">
            <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
            <span class="order-time"><?= date('H:i', strtotime($cmd['dates']['commande'])) ?></span>
        </div>
        <div class="card-client">
            <span class="client-name"><?= htmlspecialchars($cmd['id_client']) ?></span>
            <span class="order-type <?= $cmd['type'] ?>"><?= ucfirst(str_replace('_', ' ', $cmd['type'])) ?></span>
        </div>
        <div class="card-items">
            <?php foreach ($cmd['articles'] as $art): ?>
            <div class="item-line">
                <span class="item-qty"><?= $art['quantite'] ?>×</span>
                <span class="item-name"><?= htmlspecialchars($art['id']) ?></span>
                <span style="margin-left:auto;color:#bc9c64;"><?= $art['prix_unitaire'] * $art['quantite'] ?>€</span>
            </div>
            <?php endforeach; ?>
            <div style="border-top:1px solid #222;margin-top:8px;padding-top:8px;font-weight:700;color:#bc9c64;">
                Total : <?= $cmd['prix_total'] ?>€
            </div>
        </div>
        <div class="card-actions">
            <a href="../actions/statut_commande.php?id=<?= $cmd['id'] ?>&statut=<?= $nextStatut ?>" class="btn-action <?= $btnClass ?>">
                <?= $btnLabel ?>
            </a>
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
            <div class="stat-pill">
                <span class="stat-num"><?= count($attente) ?></span>
                <span class="stat-txt">En attente</span>
            </div>
            <div class="stat-pill accent">
                <span class="stat-num"><?= count($cuisine) ?></span>
                <span class="stat-txt">En cuisine</span>
            </div>
            <div class="stat-pill green">
                <span class="stat-num"><?= count($pret) ?></span>
                <span class="stat-txt">Prêtes</span>
            </div>
            <div class="stat-pill muted">
                <span class="stat-num"><?= count($livraison) ?></span>
                <span class="stat-txt">En livraison</span>
            </div>
        </div>
    </div>

    <main class="kanban">

        <div class="kanban-col">
            <div class="col-header waiting">
                <div class="col-dot"></div>
                <h2>En attente</h2>
                <span class="col-count"><?= count($attente) ?></span>
            </div>
            <div class="cards-list">
                <?php afficherCartes($attente, 'Prendre en charge', 'primary', 'en_preparation'); ?>
                <?php if (empty($attente)): ?>
                    <p style="color:#444;font-size:0.8rem;text-align:center;padding:20px;">Aucune commande</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header cooking">
                <div class="col-dot"></div>
                <h2>En cuisine</h2>
                <span class="col-count"><?= count($cuisine) ?></span>
            </div>
            <div class="cards-list">
                <?php afficherCartes($cuisine, 'Marquer prêt', 'success', 'pret'); ?>
                <?php if (empty($cuisine)): ?>
                    <p style="color:#444;font-size:0.8rem;text-align:center;padding:20px;">Aucune commande</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header ready">
                <div class="col-dot"></div>
                <h2>Prêtes</h2>
                <span class="col-count"><?= count($pret) ?></span>
            </div>
            <div class="cards-list">
                <?php afficherCartes($pret, 'Envoyer en livraison', 'gold', 'en_livraison'); ?>
                <?php if (empty($pret)): ?>
                    <p style="color:#444;font-size:0.8rem;text-align:center;padding:20px;">Aucune commande</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="kanban-col">
            <div class="col-header delivering">
                <div class="col-dot"></div>
                <h2>En livraison</h2>
                <span class="col-count"><?= count($livraison) ?></span>
            </div>
            <div class="cards-list">
                <?php foreach ($livraison as $cmd): ?>
                <article class="order-card">
                    <div class="card-top">
                        <span class="order-id"><?= htmlspecialchars($cmd['id']) ?></span>
                        <span class="order-time"><?= date('H:i', strtotime($cmd['dates']['commande'])) ?></span>
                    </div>
                    <div class="card-client">
                        <span class="client-name"><?= htmlspecialchars($cmd['id_client']) ?></span>
                    </div>
                    <div class="card-items">
                        <div class="livreur-info">
                            <span class="livreur-icon">🛵</span>
                            <span><?= htmlspecialchars($cmd['id_livreur'] ?? 'Non assigné') ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="btn-action muted" disabled>En route...</button>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php if (empty($livraison)): ?>
                    <p style="color:#444;font-size:0.8rem;text-align:center;padding:20px;">Aucune commande</p>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <script src="../js/commande.js"></script>
</body>
</html>