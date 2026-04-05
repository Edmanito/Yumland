<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

if (estConnecte()) {
    header('Location: profil.php');
    exit;
}

$erreurs = [
    'champs_vides'   => 'Veuillez remplir tous les champs obligatoires.',
    'email_invalide' => 'L\'adresse email n\'est pas valide.',
    'mdp_court'      => 'Le mot de passe doit contenir au moins 8 caractères.',
    'email_existe'   => 'Cette adresse email est déjà utilisée.',
    'erreur_serveur' => 'Une erreur est survenue, veuillez réessayer.'
];
$erreur = isset($_GET['erreur']) ? ($erreurs[$_GET['erreur']] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@200;300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/commun.css">
    <link rel="stylesheet" href="../css/inscription.css">
</head>
<body class="page-inscription">

    <div class="inscription-wrapper">

        <div class="inscription-image">
            <div class="image-brand-overlay">
                <h2>Kaiseki <em>Shunei</em></h2>
                <p>L'ART DE LA PERFECTION JAPONAISE</p>
            </div>
        </div>

        <div class="inscription-form-side">
            <div class="form-inner">

                <a href="../index.php" class="btn-back">← RETOUR À L'ACCUEIL</a>

                <span class="pre-title">BIENVENUE CHEZ SHUNEI</span>
                <h1>Créer un compte</h1>
                <p class="form-intro">Rejoignez l'excellence Shunei pour vos réservations et commandes.</p>

                <?php if ($erreur): ?>
                    <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <form action="../actions/register.php" method="POST" novalidate>

                    <div class="form-section">
                        <div class="section-label">Identité</div>
                        <div class="grid-2">
                            <div class="input-group">
                                <label>Prénom <span class="required">*</span></label>
                                <input type="text" name="prenom" placeholder="Jean" required autocomplete="given-name">
                            </div>
                            <div class="input-group">
                                <label>Nom <span class="required">*</span></label>
                                <input type="text" name="nom" placeholder="Dupont" required autocomplete="family-name">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Téléphone <span class="required">*</span></label>
                            <input type="tel" name="telephone" placeholder="06 12 34 56 78" required autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">Connexion</div>
                        <div class="input-group">
                            <label>Adresse email <span class="required">*</span></label>
                            <input type="email" name="login" placeholder="jean.dupont@email.com" required autocomplete="email">
                        </div>
                        <div class="input-group">
                            <label>Mot de passe <span class="required">*</span></label>
                            <input type="password" name="mdp" placeholder="••••••••" required minlength="8" autocomplete="new-password">
                            <div class="password-strength">
                                <div class="password-strength-bar"></div>
                            </div>
                            <span class="strength-text"></span>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-label">Adresse de livraison</div>
                        <div class="input-group">
                            <label>Rue <span class="required">*</span></label>
                            <input type="text" name="adresse_rue" placeholder="45 Rue de l'Amiral Mouchez" required autocomplete="street-address">
                        </div>
                        <div class="grid-3">
                            <div class="input-group">
                                <label>Ville <span class="required">*</span></label>
                                <input type="text" name="adresse_ville" placeholder="Paris" required autocomplete="address-level2">
                            </div>
                            <div class="input-group">
                                <label>Code postal <span class="required">*</span></label>
                                <input type="text" name="adresse_cp" placeholder="75013" required maxlength="5" autocomplete="postal-code">
                            </div>
                            <div class="input-group">
                                <label>Arrondissement</label>
                                <select name="arrondissement">
                                    <option value="">—</option>
                                    <?php for ($i = 1; $i <= 20; $i++): ?>
                                        <option value="<?= $i ?>">
                                            <?= $i ?><?= $i === 1 ? 'er' : 'e' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="grid-2">
                            <div class="input-group">
                                <label>Étage</label>
                                <input type="text" name="etage" placeholder="4e étage">
                            </div>
                            <div class="input-group">
                                <label>Interphone / Digicode</label>
                                <input type="text" name="interphone" placeholder="A1234">
                            </div>
                        </div>
                    </div>

                    <div class="btn-submit-wrap">
                        <button type="submit" class="btn-submit">
                            <span>CRÉER MON COMPTE</span>
                        </button>
                    </div>

                </form>

                <p class="switch-link">Déjà un compte ? <a href="../index.php">Se connecter</a></p>

            </div>
        </div>

    </div>

    <script src="../js/inscription.js"></script>

</body>
</html>