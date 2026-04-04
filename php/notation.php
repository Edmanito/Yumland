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
        <a href="../index.php" class="btn-retour"><span>←</span> RETOUR ACCUEIL</a>
    </nav>

    <main class="notation-container">
        <section class="notation-card">
            <header class="notation-header">
                <span class="pre-title">L'ART DE LA CRITIQUE</span>
                <h1>Votre Expérience</h1>
                <p>Comment évaluez-vous votre voyage culinaire ?</p>
            </header>

            <form id="ratingForm" class="notation-form">
                <div class="stars-wrapper">
                    <p class="label-stars">Note de la dégustation</p>
                    <div class="stars">
                        <input type="radio" name="star" id="star5" value="5"><label for="star5">★</label>
                        <input type="radio" name="star" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="star" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="star" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="star" id="star1" value="1"><label for="star1">★</label>
                    </div>
                </div>

                <div class="comment-box">
                    <label for="comment">Vos impressions (Optionnel)</label>
                    <textarea id="comment" name="comment" rows="4" placeholder="Partagez vos émotions avec le Chef..."></textarea>
                </div>

                <button type="submit" class="btn-send">TRANSMETTRE AU CHEF</button>
            </form>
        </section>
    </main>

    <script src="../js/notation.js"></script>
</body>
</html>