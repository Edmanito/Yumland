<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

$prenom = isset($_SESSION['user']['infos']['prenom']) ? htmlspecialchars($_SESSION['user']['infos']['prenom']) : "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arigato | Kaiseki Shunei</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gold: #bc9c64;
            --dark-bg: #050505;
            --card-bg: #0a0a0a;
        }

        body { 
            background: var(--dark-bg); 
            color: var(--gold); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            text-align: center; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            overflow: hidden; 
        }

        .kanji-background { 
            font-size: 15rem; 
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%); 
            opacity: 0.03; 
            font-family: serif; 
            pointer-events: none; 
            white-space: nowrap;
        }

        .thank-you-card { 
            border: 1px solid rgba(188, 156, 100, 0.2); 
            padding: 60px 40px; 
            background: linear-gradient(145deg, #0a0a0a 0%, #111 100%); 
            position: relative; 
            max-width: 550px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            z-index: 1;
        }

        h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 3.5rem; 
            letter-spacing: 10px; 
            margin: 0 0 20px 0; 
            color: var(--gold);
            text-transform: uppercase;
        }

        .separator {
            width: 50px;
            height: 1px;
            background: var(--gold);
            margin: 20px auto;
            opacity: 0.5;
        }

        p { 
            font-size: 1.1rem;
            opacity: 0.8; 
            line-height: 1.8; 
            margin-bottom: 45px; 
            font-weight: 300; 
            letter-spacing: 1px;
        }

        .btn-retour { 
            border: 1px solid var(--gold); 
            color: var(--gold); 
            text-decoration: none; 
            padding: 18px 40px; 
            font-size: 0.75rem; 
            letter-spacing: 3px; 
            font-weight: 600; 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); 
            display: inline-block; 
            text-transform: uppercase;
        }

        .btn-retour:hover { 
            background: var(--gold); 
            color: #000; 
            box-shadow: 0 0 30px rgba(188, 156, 100, 0.3);
            transform: translateY(-2px);
        }

        .thank-you-card {
            animation: fadeIn 1.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="kanji-background">感謝</div>

    <div class="thank-you-card">
        <span style="font-size: 0.8rem; letter-spacing: 5px; opacity: 0.6;">MESSAGE TRANSMIS</span>
        <h1>ARIGATO</h1>
        <div class="separator"></div>
        
        <p>
            <?php if ($prenom): ?>
                Cher(e) <strong><?= $prenom ?></strong>, <br>
            <?php endif; ?>
            Votre précieux retour a été transmis à nos Maîtres de cuisine.<br>
            C'est grâce à votre regard que notre art continue de s'affiner.
        </p>

        <a href="profil.php" class="btn-retour">Retour à mon espace</a>
    </div>

</body>
</html>