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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/commun.css">
    <link rel="stylesheet" href="../css/inscription.css">
    <style>
        .page-inscription { background: #080808; color: white; min-height: 100vh; display: flex; }
        .inscription-wrapper { display: flex; width: 100%; min-height: 100vh; }
        .inscription-image { flex: 1; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('../img/fondHeader.png') center/cover; }
        .inscription-form-side { flex: 1.2; background: #0a0a0a; display: flex; align-items: center; justify-content: center; padding: 60px; overflow-y: auto; border-left: 1px solid rgba(197,160,89,0.2); }
        .form-inner { max-width: 500px; width: 100%; }
        .btn-back { color: #bc9c64; text-decoration: none; font-size: 0.7rem; letter-spacing: 2px; display: block; margin-bottom: 30px; }
        .pre-title { display: block; font-size: 0.65rem; letter-spacing: 4px; color: #bc9c64; margin-bottom: 10px; }
        h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 8px; }
        .form-intro { color: #888; font-size: 0.85rem; margin-bottom: 30px; }
        .alert-error { background: rgba(255,70,70,0.1); border: 1px solid rgba(255,70,70,0.3); color: #ff6b6b; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; border-radius: 4px; }
        .input-row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .input-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 20px; }
        .input-group label { font-size: 0.65rem; letter-spacing: 2px; color: #bc9c64; text-transform: uppercase; }
        .hint { color: #666; text-transform: none; letter-spacing: 0; }
        .input-group input { background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.15); color: white; padding: 12px 0; outline: none; font-family: inherit; font-size: 0.9rem; transition: border-color 0.3s; }
        .input-group input:focus { border-bottom-color: #bc9c64; }
        .btn-submit { width: 100%; padding: 16px; background: transparent; border: 1px solid #bc9c64; color: #bc9c64; font-size: 0.8rem; font-weight: 700; letter-spacing: 3px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #bc9c64; color: #000; }
        .switch-link { text-align: center; margin-top: 20px; font-size: 0.8rem; color: #666; }
        .switch-link a { color: #bc9c64; text-decoration: none; }
        @media(max-width: 768px) { .inscription-image { display: none; } .inscription-form-side { padding: 30px 20px; } }
    </style>
</head>
<body class="page-inscription">
    <div class="inscription-wrapper">
        <div class="inscription-image"></div>
        <div class="inscription-form-side">
            <div class="form-inner">
                <a href="../index.php" class="btn-back">← Retour à l'accueil</a>
                <span class="pre-title">BIENVENUE CHEZ SHUNEI</span>
                <h1>Créer un compte</h1>
                <p class="form-intro">Rejoignez l'excellence Shunei pour vos réservations et livraisons.</p>

                <?php if ($erreur): ?>
                    <div class="alert-error"><?= $erreur ?></div>
                <?php endif; ?>

                <form action="../actions/register.php" method="POST">
                    <div class="input-row-grid">
                        <div class="input-group">
                            <label>Prénom *</label>
                            <input type="text" name="prenom" placeholder="Jean" required>
                        </div>
                        <div class="input-group">
                            <label>Nom *</label>
                            <input type="text" name="nom" placeholder="Dupont" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Email *</label>
                        <input type="email" name="login" placeholder="jean.dupont@email.com" required>
                    </div>
                    <div class="input-group">
                        <label>Téléphone *</label>
                        <input type="tel" name="telephone" placeholder="06 12 34 56 78" required>
                    </div>
                    <div class="input-group">
                        <label>Adresse complète *</label>
                        <input type="text" name="adresse" placeholder="12 Rue du Sushi, 75000 Paris" required>
                    </div>
                    <div class="input-row-grid">
                        <div class="input-group">
                            <label>Étage</label>
                            <input type="text" name="etage" placeholder="4">
                        </div>
                        <div class="input-group">
                            <label>Interphone</label>
                            <input type="text" name="interphone" placeholder="1234">
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Mot de passe * <span class="hint">(8 car. min.)</span></label>
                        <input type="password" name="mdp" placeholder="••••••••" required minlength="8">
                    </div>
                    <button type="submit" class="btn-submit">CRÉER MON COMPTE</button>
                </form>

                <p class="switch-link">Déjà un compte ? <a href="../index.php">Se connecter</a></p>
            </div>
        </div>
    </div>
</body>
    <script src="js/inscription.js"></script>



</html>