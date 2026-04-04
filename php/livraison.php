<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

//requireRole('livreur');

$livreur = $_SESSION['user'];

// Récupérer les commandes assignées à ce livreur
$dataCommandes = lireJSON(JSON_COMMANDES);
$mesLivraisons = array_values(array_filter(
    $dataCommandes['commandes'] ?? [],
    fn($c) => $c['id_livreur'] === $livreur['id'] && $c['statut'] === 'en_livraison'
));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Livreur | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/livraison.css">
</head>
<body class="page-delivery">

    <header class="delivery-nav">
        <div class="status-indicator">
            <span class="pulse"></span>
            <h1>MISSION EN COURS</h1>
        </div>
        <a href="../actions/logout.php" class="btn-exit">QUITTER</a>
    </header>

    <main class="delivery-container">

        <?php if (empty($mesLivraisons)): ?>
            <div style="text-align:center;padding:60px 20px;color:#666;">
                <p style="font-size:1.2rem;margin-bottom:10px;">Aucune livraison assignée</p>
                <p style="font-size:0.85rem;">En attente d'une nouvelle mission...</p>
            </div>
        <?php endif; ?>

        <?php foreach ($mesLivraisons as $cmd):
            // Récupérer les infos du client
            $dataUsers = lireJSON(JSON_USERS);
            $client = null;
            foreach ($dataUsers['utilisateurs'] as $u) {
                if ($u['id'] === $cmd['id_client']) { $client = $u; break; }
            }
            $adresse = $cmd['adresse_livraison'] ?? ($client['infos']['adresse'] ?? '');
            $adresseEncode = urlencode($adresse);
        ?>
        <article class="delivery-card ready" id="cmd-<?= $cmd['id'] ?>">
            <div class="card-header">
                <span class="order-number"><?= htmlspecialchars($cmd['id']) ?></span>
                <span class="status-pill">EN LIVRAISON</span>
            </div>

            <section class="client-info">
                <h2 class="client-name">
                    <?= $client ? htmlspecialchars($client['infos']['prenom'] . ' ' . $client['infos']['nom']) : 'Client inconnu' ?>
                </h2>
                <address class="client-address"><?= htmlspecialchars($adresse) ?></address>
                <?php if ($cmd['etage'] || $cmd['interphone']): ?>
                <div class="delivery-instructions">
                    <p>"
                        <?= $cmd['etage'] ? 'Étage : ' . htmlspecialchars($cmd['etage']) . '. ' : '' ?>
                        <?= $cmd['interphone'] ? 'Code : ' . htmlspecialchars($cmd['interphone']) . '.' : '' ?>
                    "</p>
                </div>
                <?php endif; ?>
            </section>

            <section class="order-details">
                <div class="items">
                    <?php foreach ($cmd['articles'] as $art): ?>
                        <p><?= $art['quantite'] ?>× <?= htmlspecialchars($art['id']) ?></p>
                    <?php endforeach; ?>
                </div>
                <div class="payment-status paid">
                    TOTAL : <?= $cmd['prix_total'] ?>€
                    <span><?= $cmd['paiement']['statut'] === 'paye' ? 'PAYÉ' : 'NON PAYÉ' ?></span>
                </div>
            </section>

            <footer class="action-grid">
                <a href="https://www.google.com/maps/search/?api=1&query=<?= $adresseEncode ?>" target="_blank" class="btn-secondary">NAVIGUER</a>
                <?php if ($client && $client['infos']['telephone']): ?>
                    <a href="tel:<?= htmlspecialchars($client['infos']['telephone']) ?>" class="btn-secondary">APPELER</a>
                <?php else: ?>
                    <button disabled class="btn-secondary" style="opacity:0.4;">PAS DE TEL</button>
                <?php endif; ?>
                <a href="../actions/livrer.php?id=<?= $cmd['id'] ?>" class="btn-main" onclick="return confirm('Confirmer la livraison ?')">TERMINER</a>
            </footer>
        </article>
        <?php endforeach; ?>

    </main>

    <script src="../js/livraison.js"></script>
</body>
</html>