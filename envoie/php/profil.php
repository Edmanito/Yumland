<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireConnexion();

$user = $_SESSION['user'];

$dataCommandes = lireJSON(JSON_COMMANDES);
$mesCommandes = array_filter(
    $dataCommandes['commandes'] ?? [],
    fn($c) => $c['id_client'] === $user['id']
);
$mesCommandes = array_reverse(array_values($mesCommandes));

$labelStatuts = [
    'en_attente'     => 'En attente',
    'en_preparation' => 'En préparation',
    'pret'           => 'Prêt',
    'en_livraison'   => 'En livraison',
    'livree'         => 'Livré'
];

$classStatuts = [
    'en_attente'     => 'waiting',
    'en_preparation' => 'cooking',
    'pret'           => 'ready',
    'en_livraison'   => 'delivering',
    'livree'         => 'done'
];

$pct = min(100, round(($user['fidelite']['points'] / 1000) * 100));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/profil.css">
    <style>
        .btn-logout { color: #bc9c64; text-decoration: none; font-size: 0.7rem; letter-spacing: 2px; border-bottom: 1px solid #bc9c64; }
        .back-link { color: white; text-decoration: none; }
        .remise { color: #4caf50; font-weight: bold; }
        .no-orders { color: #666; font-style: italic; padding: 20px 0; }
        .status-pill.done { background: #1a3a1a; color: #4caf50; }
        .status-pill.waiting { background: #3a2a00; color: #f59e0b; }
        .status-pill.cooking { background: #001a3a; color: #3b82f6; }
        .status-pill.ready { background: #1a3a1a; color: #22c55e; }
        .status-pill.delivering { background: #2a1a3a; color: #a855f7; }
        .progress-fill { background: #bc9c64; border-radius: 3px; }
    </style>
</head>
<body class="page-profil">

    <nav class="profil-nav">
        <a href="../index.php" class="back-link">← RETOUR AU RESTAURANT</a>
        <div class="logo-kanji-small"><span>春</span><span>栄</span></div>
        <a href="../actions/logout.php" class="btn-logout">DÉCONNEXION</a>
    </nav>

    <div class="profil-container">
        <header class="profil-header">
            <div class="header-main">
                <h1>BIENVENUE, <?= strtoupper(htmlspecialchars($user['infos']['prenom'])) ?></h1>
                <p class="member-since">Membre depuis <?= date('d/m/Y', strtotime($user['dates']['inscription'])) ?></p>
            </div>
            <div class="loyalty-card">
                <span class="label">STATUT PRIVILÈGE</span>
                <div class="loyalty-badge"><?= htmlspecialchars($user['fidelite']['badge']) ?></div>
                <div class="points-count"><?= $user['fidelite']['points'] ?> <span>points</span></div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width:<?= $pct ?>%; height:100%;"></div>
                </div>
            </div>
        </header>

        <main class="profil-grid">
            <section class="profil-section info-section">
                <div class="section-title">
                    <h3>COORDONNÉES</h3>
                    <span class="edit-icon" title="Modification disponible en phase 3">✎</span>
                </div>
                <div class="info-card">
                    <div class="info-group">
                        <label>NOM COMPLET</label>
                        <p><?= htmlspecialchars($user['infos']['prenom'] . ' ' . $user['infos']['nom']) ?></p>
                    </div>
                    <div class="info-group">
                        <label>EMAIL</label>
                        <p><?= htmlspecialchars($user['login']) ?></p>
                    </div>
                    <div class="info-group">
                        <label>TÉLÉPHONE</label>
                        <p><?= htmlspecialchars($user['infos']['telephone'] ?: 'Non renseigné') ?></p>
                    </div>
                    <div class="info-group">
                        <label>ADRESSE DE LIVRAISON</label>
                        <p><?= htmlspecialchars($user['infos']['adresse'] ?: 'Non renseignée') ?></p>
                        <?php if ($user['infos']['etage'] || $user['infos']['interphone']): ?>
                            <p class="sub-info">
                                <?= $user['infos']['etage'] ? 'Étage ' . htmlspecialchars($user['infos']['etage']) : '' ?>
                                <?= $user['infos']['interphone'] ? ' • Code : ' . htmlspecialchars($user['infos']['interphone']) : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($user['remise'] > 0): ?>
                    <div class="info-group">
                        <label>REMISE FIDÉLITÉ</label>
                        <p class="remise"><?= $user['remise'] ?>% sur toutes vos commandes</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="profil-section orders-section">
                <h3>MES COMMANDES</h3>
                <div class="table-wrapper">
                    <?php if (empty($mesCommandes)): ?>
                        <p class="no-orders">Aucune commande pour le moment.</p>
                    <?php else: ?>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>DATE</th>
                                <th>ARTICLES</th>
                                <th>STATUT</th>
                                <th>PRIX</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mesCommandes as $cmd): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($cmd['dates']['commande'])) ?></td>
                                <td class="dish-name"><?= count($cmd['articles']) ?> article(s)</td>
                                <td>
                                    <span class="status-pill <?= $classStatuts[$cmd['statut']] ?? '' ?>">
                                        <?= $labelStatuts[$cmd['statut']] ?? $cmd['statut'] ?>
                                    </span>
                                </td>
                                <td class="price">
                                    <?= $cmd['prix_total'] ?>€
                                    <?php if ($cmd['statut'] === 'livree' && !$cmd['note_client']): ?>
                                        <a href="notation.php?cmd=<?= $cmd['id'] ?>" class="btn-note" title="Noter">★</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

</body>
</html>