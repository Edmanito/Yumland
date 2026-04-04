<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireConnexion();

$idCommande = nettoyer($_GET['cmd'] ?? '');
$userId = $_SESSION['user']['id'];

$data = lireJSON(JSON_COMMANDES);
$commande = null;
foreach ($data['commandes'] as $cmd) {
    if ($cmd['id'] === $idCommande && $cmd['id_client'] === $userId && $cmd['statut'] === 'livree') {
        $commande = $cmd;
        break;
    }
}

if (!$commande) {
    header('Location: profil.php?erreur=commande_introuvable');
    exit;
}

if ($commande['note_client'] !== null) {
    header('Location: profil.php?erreur=deja_note');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Expérience | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/notation.css">
</head>
<body class="page-notation">

    <nav class="admin-topbar">
        <a href="profil.php" class="btn-retour"><span>←</span> MON PROFIL</a>
    </nav>

    <main class="notation-container">
        <section class="notation-card">
            <header class="notation-header">
                <span class="pre-title">L'ART DE LA CRITIQUE</span>
                <h1>Votre Expérience</h1>
                <p>Commande <?= htmlspecialchars($commande['id']) ?> — Comment évaluez-vous votre voyage culinaire ?</p>
            </header>

            <form method="POST" action="../actions/noter.php" class="notation-form">
                <input type="hidden" name="cmd" value="<?= htmlspecialchars($idCommande) ?>">

                <div class="stars-wrapper">
                    <p class="label-stars">Note des produits</p>
                    <div class="stars">
                        <input type="radio" name="note_produits" id="prod5" value="5" required><label for="prod5">★</label>
                        <input type="radio" name="note_produits" id="prod4" value="4"><label for="prod4">★</label>
                        <input type="radio" name="note_produits" id="prod3" value="3"><label for="prod3">★</label>
                        <input type="radio" name="note_produits" id="prod2" value="2"><label for="prod2">★</label>
                        <input type="radio" name="note_produits" id="prod1" value="1"><label for="prod1">★</label>
                    </div>
                </div>

                <div class="stars-wrapper">
                    <p class="label-stars">Note de la livraison</p>
                    <div class="stars">
                        <input type="radio" name="note_livraison" id="liv5" value="5" required><label for="liv5">★</label>
                        <input type="radio" name="note_livraison" id="liv4" value="4"><label for="liv4">★</label>
                        <input type="radio" name="note_livraison" id="liv3" value="3"><label for="liv3">★</label>
                        <input type="radio" name="note_livraison" id="liv2" value="2"><label for="liv2">★</label>
                        <input type="radio" name="note_livraison" id="liv1" value="1"><label for="liv1">★</label>
                    </div>
                </div>

                <div class="comment-box">
                    <label for="commentaire">Vos impressions (Optionnel)</label>
                    <textarea id="commentaire" name="commentaire" rows="4" placeholder="Partagez vos émotions avec le Chef..."></textarea>
                </div>

                <button type="submit" class="btn-send">TRANSMETTRE AU CHEF</button>
            </form>
        </section>
    </main>

</body>
</html>