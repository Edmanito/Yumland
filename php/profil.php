<?php
// On force l'UTF-8 pour éviter les problèmes d'accents
header('Content-Type: text/html; charset=utf-8');

require_once '../includes/config.php';
require_once '../includes/fonctions.php';

requireConnexion();

$currentUser = $_SESSION['user'];
$userIdToDisplay = $_GET['id'] ?? null;

$user = $currentUser;

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
    'livree'         => 'Livré',
    'annulee'        => 'Annulée'
];

$classStatuts = [
    'en_attente'     => 'waiting',
    'en_preparation' => 'cooking',
    'pret'           => 'ready',
    'en_livraison'   => 'delivering',
    'livree'         => 'done',
    'annulee'        => 'canceled'
];

$pct = min(100, round(($user['fidelite']['points'] / 1000) * 100));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <script>
        // Appliquer le thème AVANT le rendu pour éviter le flash
        (function(){
            const m = document.cookie.match(/(?:^|; )kaiseki_theme=([^;]*)/);
         const t = m ? decodeURIComponent(m[1]) : 'sombre';
            if (t === 'clair') {
             const l = document.createElement('link');
             l.rel = 'stylesheet'; l.id = 'theme-stylesheet';
             l.href = '../css/theme-clair.css';
                document.head.appendChild(l);
            }
        })();
    </script>
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
        .status-pill.canceled { background: #3a1a1a; color: #ef4444; }
        .progress-fill { background: #bc9c64; border-radius: 3px; }
        .admin-view-tag { background: #bc9c64; color: black; padding: 2px 8px; font-size: 0.7rem; border-radius: 10px; margin-left: 10px; vertical-align: middle; }
        
        /* Styles pour la modale d'édition */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-content { background: #111; border: 1px solid #bc9c64; padding: 30px; border-radius: 5px; width: 90%; max-width: 500px; position: relative; color: white; }
        .close-modal { position: absolute; top: 15px; right: 20px; cursor: pointer; font-size: 1.5rem; color: #bc9c64; }
        .modal-content .input-group { margin-bottom: 15px; }
        .modal-content label { display: block; font-size: 0.8rem; margin-bottom: 5px; color: #bc9c64; }
        .modal-content input { width: 100%; padding: 10px; background: rgba(255,255,255,0.1); border: 1px solid #333; color: white; box-sizing: border-box; }
        .modal-content .btn-submit { width: 100%; padding: 15px; background: #bc9c64; color: black; border: none; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .char-counter { font-size: 0.75rem; text-align: right; color: #888; margin-top: 2px; }
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
                    <?php if ($user['id'] === $currentUser['id']): ?>
                        <span class="edit-icon" id="btn-edit-profile" style="cursor: pointer;" title="Modifier mes informations">✎</span>
                    <?php endif; ?>
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
                        <p><?= !empty($user['infos']['adresse']) ? htmlspecialchars($user['infos']['adresse']) : '<i style="color:#888;">Non renseignée</i>' ?></p>
                        <?php if (!empty($user['infos']['etage']) || !empty($user['infos']['interphone'])): ?>
                            <p class="sub-info" style="font-size: 0.85rem; color: #aaa; margin-top: 5px;">
                                <?= !empty($user['infos']['etage']) ? 'Étage/Appt : ' . htmlspecialchars($user['infos']['etage']) : '' ?>
                                <?= !empty($user['infos']['etage']) && !empty($user['infos']['interphone']) ? ' • ' : '' ?>
                                <?= !empty($user['infos']['interphone']) ? 'Interphone : ' . htmlspecialchars($user['infos']['interphone']) : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (($user['remise'] ?? 0) > 0): ?>
                    <div class="info-group">
                        <label>REMISE FIDÉLITÉ</label>
                        <p class="remise"><?= $user['remise'] ?>% sur toutes vos commandes</p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($user['tickets_reduction'])): ?>
                    <div class="info-group">
                        <label>TICKETS DE RÉDUCTION DISPONIBLES</label>
                        <?php foreach ($user['tickets_reduction'] as $ticket): ?>
                            <p style="color: #bc9c64; font-weight: bold; margin-bottom: 5px;">
                                🎟️ <?= $ticket['montant'] ?>€ 
                                <span style="font-size: 0.7rem; color: #888; font-weight: normal;">(<?= htmlspecialchars($ticket['origine']) ?>)</span>
                            </p>
                        <?php endforeach; ?>
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
                                    
                                    <?php if ($cmd['statut'] === 'livree'): ?>
                                        <?php if (empty($cmd['note_client'])): ?>
                                            <?php if ($user['id'] === $currentUser['id']): ?>
                                                <a href="notation.php?cmd=<?= $cmd['id'] ?>" class="btn-note" title="Noter" style="margin-left: 10px; color: #bc9c64; text-decoration: none; font-size: 1.2rem;">★</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn-view-avis" 
                                                    data-avis="<?= htmlspecialchars(json_encode($cmd['note_client'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                    style="background:none; border:none; cursor:pointer; font-size:1.2rem; margin-left: 10px; vertical-align: middle;" 
                                                    title="Voir mon avis">
                                                👁️
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($user['id'] === $currentUser['id'] && $cmd['statut'] === 'en_attente'): ?>
                                        <button class="btn-modifier-cmd" 
                                                data-id="<?= $cmd['id'] ?>" 
                                                data-prix="<?= $cmd['prix_total'] ?>" 
                                                data-articles='<?= json_encode($cmd['articles'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                                title="Modifier ma commande"
                                                style="background:none; border:1px solid #bc9c64; color:#bc9c64; padding:5px 10px; cursor:pointer; font-size:0.7rem; border-radius:3px; margin-left:10px;">
                                            ✎ MODIFIER
                                        </button>
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

    <div id="edit-profile-modal" class="modal-overlay">
        <div class="modal-content" style="max-height: 90vh; overflow-y: auto;">
            <span class="close-modal" id="close-edit-modal">✕</span>
            <h2 style="font-family:'Playfair Display'; color:#bc9c64; margin-bottom: 20px;">Modifier mon profil</h2>
            
            <form id="form-edit-profile" novalidate>
                <div class="input-group">
                    <label>Nouvelle adresse email</label>
                    <input type="email" name="login" value="<?= htmlspecialchars($user['login']) ?>" required maxlength="50">
                    <div class="char-counter" id="counter-edit-email">0 / 50</div>
                </div>
                
                <div class="input-group">
                    <label>Nouveau mot de passe (laisser vide pour ne rien changer)</label>
                    <div style="position: relative;">
                        <input type="password" name="mdp" placeholder="••••••••" maxlength="30" style="padding-right: 40px;">
                        <button type="button" id="toggleEditPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2em;">👁️</button>
                    </div>
                    <div class="char-counter" id="counter-edit-mdp">0 / 30</div>
                </div>

                <div class="input-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" value="<?= htmlspecialchars($user['infos']['telephone'] ?? '') ?>" required>
                </div>

                <div class="input-group">
                    <label>Adresse de livraison</label>
                    <input type="text" name="adresse" value="<?= htmlspecialchars($user['infos']['adresse'] ?? '') ?>" placeholder="N°, Rue, Ville...">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="input-group" style="flex: 1;">
                        <label>Étage / Appt</label>
                        <input type="text" name="etage" value="<?= htmlspecialchars($user['infos']['etage'] ?? '') ?>" placeholder="Ex: 3ème gauche">
                    </div>
                    <div class="input-group" style="flex: 1;">
                        <label>Interphone / Code</label>
                        <input type="text" name="interphone" value="<?= htmlspecialchars($user['infos']['interphone'] ?? '') ?>" placeholder="Ex: A123B">
                    </div>
                </div>

                <button type="submit" class="btn-submit">ENREGISTRER</button>
            </form>
        </div>
    </div>

    <div id="edit-cmd-modal" class="modal-overlay">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close-modal" id="close-cmd-modal">✕</span>
            <h2 style="font-family:'Playfair Display'; color:#bc9c64; margin-bottom: 5px;">Modifier ma commande</h2>
            <p style="font-size: 0.8rem; color: #888; margin-bottom: 20px;">Commande <span id="modal-cmd-id"></span></p>
            
            <div id="modal-cmd-items" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 10px 0;">
                </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <span style="color:#888; font-size:0.9rem;">Ancien total : <span id="modal-old-price"></span>€</span>
                <span style="color:#bc9c64; font-size:1.2rem; font-weight:bold;">Nouveau total : <span id="modal-new-price"></span>€</span>
            </div>

            <div id="modal-diff-msg" style="font-size: 0.85rem; margin-bottom: 15px; padding: 10px; border-radius: 4px; display: none;">
                </div>

            <button id="btn-save-cmd" class="btn-submit">VALIDER LES MODIFICATIONS</button>
        </div>
    </div>

    <div id="modal-view-avis" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); justify-content:center; align-items:center; z-index:10000;">
        <div class="modal-content" style="background:#111; border:1px solid #bc9c64; padding:30px; border-radius:5px; width:90%; max-width:400px; position:relative; color:white; text-align:center;">
            <span class="close-modal" id="close-avis-modal" style="position:absolute; top:10px; right:15px; cursor:pointer; color:#bc9c64; font-size:1.5rem;">✕</span>
            <h2 style="font-family:'Playfair Display'; color:#bc9c64; margin-bottom: 20px;">VOTRE AVIS</h2>
            <div id="content-avis-popup"></div>
            <button id="btn-close-avis-popup" style="width:100%; padding:12px; background:#bc9c64; border:none; margin-top:20px; cursor:pointer; font-weight:bold; color: black;">FERMER</button>
        </div>
    </div>
    <script src="../js/profil.js"></script>
<script src="../js/theme.js"></script>
</body>
</html>