<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireRole('admin');

$data = lireJSON(JSON_USERS);
$utilisateurs = $data['utilisateurs'] ?? [];

// Filtre par rôle
$filtre = $_GET['role'] ?? 'all';
if ($filtre !== 'all') {
    $utilisateurs = array_filter($utilisateurs, fn($u) => $u['role'] === $filtre);
}

// Recherche
$recherche = strtolower($_GET['q'] ?? '');
if ($recherche) {
    $utilisateurs = array_filter($utilisateurs, function($u) use ($recherche) {
        return str_contains(strtolower($u['login']), $recherche)
            || str_contains(strtolower($u['infos']['nom']), $recherche)
            || str_contains(strtolower($u['infos']['prenom']), $recherche);
    });
}

$utilisateurs = array_values($utilisateurs);

// Stats
$dataAll = lireJSON(JSON_USERS)['utilisateurs'] ?? [];
$nbClients   = count(array_filter($dataAll, fn($u) => $u['role'] === 'client'));
$nbLivreurs  = count(array_filter($dataAll, fn($u) => $u['role'] === 'livreur'));
$nbSuspendus = count(array_filter($dataAll, fn($u) => $u['statut'] === 'suspendu'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="page-admin">
    <div class="admin-container">

        <header class="admin-header">
            <div class="header-main">
                <a href="../index.php" class="btn-back">← Retour</a>
                <h1>Administration</h1>
                <p class="subtitle">Gestion des membres de la maison Kaiseki</p>
            </div>
            <div class="admin-stats-bar">
                <div class="stat-card">
                    <span class="stat-value"><?= $nbClients ?></span>
                    <span class="stat-label">Clients</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= $nbLivreurs ?></span>
                    <span class="stat-label">Livreurs</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value" style="color:#ff4d4d"><?= $nbSuspendus ?></span>
                    <span class="stat-label">Suspendus</span>
                </div>
            </div>
        </header>

        <section class="controls-section">
            <form method="GET" class="search-wrapper">
                <input type="text" name="q" placeholder="Rechercher un nom, email..." value="<?= htmlspecialchars($recherche) ?>">
                <select name="role" onchange="this.form.submit()">
                    <option value="all"          <?= $filtre === 'all'          ? 'selected' : '' ?>>Tous les rôles</option>
                    <option value="client"       <?= $filtre === 'client'       ? 'selected' : '' ?>>Clients</option>
                    <option value="livreur"      <?= $filtre === 'livreur'      ? 'selected' : '' ?>>Livreurs</option>
                    <option value="admin"        <?= $filtre === 'admin'        ? 'selected' : '' ?>>Admins</option>
                    <option value="restaurateur" <?= $filtre === 'restaurateur' ? 'selected' : '' ?>>Restaurateurs</option>
                </select>
                <button type="submit" style="padding:14px 20px;background:var(--gold);border:none;color:#000;cursor:pointer;border-radius:10px;font-weight:700;">Rechercher</button>
            </form>
        </section>

        <main class="admin-content">
            <div class="table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Identité</th>
                            <th class="hide-mobile">Contact</th>
                            <th>Rôle</th>
                            <th class="hide-mobile">Statut</th>
                            <th class="actions-header">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utilisateurs)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#666;padding:40px;">Aucun utilisateur trouvé.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($utilisateurs as $u): ?>
                        <tr class="user-row <?= $u['statut'] === 'suspendu' ? 'blocked' : '' ?>">
                            <td>
                                <div class="user-info">
                                    <div class="avatar <?= $u['role'] === 'livreur' ? 'livreur-av' : '' ?>">
                                        <?= strtoupper(substr($u['infos']['prenom'], 0, 1) . substr($u['infos']['nom'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <span class="user-name"><?= htmlspecialchars($u['infos']['prenom'] . ' ' . $u['infos']['nom']) ?></span>
                                        <span class="user-id"><?= htmlspecialchars($u['id']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-mobile email-cell"><?= htmlspecialchars($u['login']) ?></td>
                            <td><span class="badge <?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td class="hide-mobile">
                                <?php if ($u['statut'] === 'actif'): ?>
                                    <span class="status-tag active"><span class="dot"></span> Actif</span>
                                <?php elseif ($u['statut'] === 'suspendu'): ?>
                                    <span class="status-tag suspended"><span class="dot" style="background:#ff4d4d"></span> Suspendu</span>
                                <?php else: ?>
                                    <span class="status-tag"><span class="dot"></span> <?= htmlspecialchars($u['statut']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <div class="btn-group">
                                    <?php if ($u['role'] === 'client'): ?>
                                        <a href="profil.php?id=<?= $u['id'] ?>" class="btn-icon view" title="Voir Profil">👁</a>
                                    <?php endif; ?>
                                    <a href="../actions/bloquer.php?id=<?= $u['id'] ?>" class="btn-icon block-toggle" title="<?= $u['statut'] === 'suspendu' ? 'Débloquer' : 'Bloquer' ?>">
                                        <?= $u['statut'] === 'suspendu' ? '✅' : '🚫' ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <p style="text-align:right;margin-top:20px;color:#555;font-size:0.75rem;">
            <?= count($utilisateurs) ?> utilisateur(s) affiché(s)
        </p>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>