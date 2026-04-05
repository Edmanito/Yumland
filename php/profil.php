<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireConnexion();

// --- LOGIQUE DE SÉLECTION DE L'UTILISATEUR ---
$currentUser = $_SESSION['user'];
$userIdToDisplay = $_GET['id'] ?? null;

// Par défaut, on affiche l'utilisateur connecté
$user = $currentUser;

// Si un ID est passé et que l'utilisateur est admin, on cherche cet utilisateur dans le JSON
if ($userIdToDisplay && ($currentUser['role'] === 'admin')) {
    $dataUsers = lireJSON(JSON_USERS);
    $utilisateurs = $dataUsers['utilisateurs'] ?? [];
    
    foreach ($utilisateurs as $u) {
        if ($u['id'] === $userIdToDisplay) {
            $user = $u;
            break;
        }
    }
}

// Récupération des commandes de l'utilisateur affiché
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
    <title><?= ($user['id'] === $currentUser['id']) ? 'Mon Profil' : 'Profil de ' . htmlspecialchars($user['infos']['nom']) ?> | Kaiseki Shunei</title>
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
        .admin-view-tag { background: #bc9c64; color: black; padding: 2px 8px; font-size: 0.7rem; border-radius: 10px; margin-left: 10px; vertical-align: middle; }
    </style>
</head>
<body class="page-profil">

    <nav class="profil-nav">
        <a href="<?= ($user['id'] !== $currentUser['id']) ? 'admin.php' : '../index.php' ?>" class="back-link">
            ← <?= ($user['id'] !== $currentUser['id']) ? 'RETOUR À L\'ADMIN' : 'RETOUR AU RESTAURANT' ?>
        </a>
        <div class="logo-kanji-small"><span>春</span><span>栄</span></div>
        <a href="../actions/logout.php" class="btn-logout">DÉCONNEXION</a>
    </nav>

    <div class="profil-container">
        <header class="profil-header">
            <div class="header-main">
                <h1>
                    <?= ($user['id'] === $currentUser['id']) ? 'BIENVENUE, ' : 'PROFIL DE ' ?>
                    <?= strtoupper(htmlspecialchars($user['infos']['prenom'])) ?>
                    <?php if($user['id'] !== $currentUser['id']): ?> <span class="admin-view-tag">ADMIN VIEW</span> <?php endif; ?>
                </h1>
                <p class="member-since">Membre depuis <?= date('d/m/Y', strtotime($user['dates']['inscription'] ?? 'today')) ?></p>
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
                        <p><?= htmlspecialchars($user['infos']['telephone'] ?? 'Non renseigné') ?></p>
                    </div>
                    <div class="info-group">
                        <label>ADRESSE DE LIVRAISON</label>
                        <p><?= htmlspecialchars($user['infos']['adresse'] ?? 'Non renseignée') ?></p>
                        <?php if (isset($user['infos']['etage']) || isset($user['infos']['interphone'])): ?>
                            <p class="sub-info">
                                <?= !empty($user['infos']['etage']) ? 'Étage ' . htmlspecialchars($user['infos']['etage']) : '' ?>
                                <?= !empty($user['infos']['interphone']) ? ' • Code : ' . htmlspecialchars($user['infos']['interphone']) : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php if (($user['remise'] ?? 0) > 0): ?>
                    <div class="info-group">
                        <label>REMISE FIDÉLITÉ</label>
                        <p class="remise"><?= $user['remise'] ?>% sur toutes vos commandes</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="profil-section orders-section">
                <h3><?= ($user['id'] === $currentUser['id']) ? 'MES COMMANDES' : 'HISTORIQUE DU CLIENT' ?></h3>
                <div class="table-wrapper">
                    <?php if (empty($mesCommandes)): ?>
                        <p class="no-orders">Aucune commande enregistrée.</p>
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
                                    <?php if ($user['id'] === $currentUser['id'] && $cmd['statut'] === 'livree' && empty($cmd['note_client'])): ?>
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