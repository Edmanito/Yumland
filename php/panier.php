<?php
require_once '../includes/config.php';
require_once '../includes/fonctions.php';

$plats = lireJSON(JSON_PLATS)['plats'] ?? [];
$menus = lireJSON(JSON_MENUS)['menus'] ?? [];
$tousLesProduits = array_merge($plats, $menus);

$totalCommande = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Sélection | <?= SITE_NOM ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/panier.css">
</head>
<body class="page-panier">

<?php if (!estConnecte()): ?>

    <div class="panier-auth">
        <div class="auth-box-panier">
            <span class="kanji">買物籠</span>
            <h2>Votre panier vous attend</h2>
            <p>Connectez-vous ou créez un compte pour accéder à votre sélection et procéder au règlement.</p>
            <div class="auth-btns">
                <a href="../index.php" class="btn-auth-connexion">SE CONNECTER</a>
                <a href="inscription.php" class="btn-auth-inscription">CRÉER UN COMPTE</a>
            </div>
            <p class="auth-note">Vos articles sont conservés pendant votre session.</p>
        </div>
    </div>

<?php else: ?>

    <div class="panier-container">
        <header class="panier-header">
            <a href="carte.php" class="back-link">← CONTINUER VOS ACHATS</a>
            <h1>Votre Sélection</h1>
            <div class="gold-line"></div>
        </header>

        <?php if (empty($_SESSION['panier'])): ?>

            <div class="panier-vide">
                <p>Votre panier est aussi pur que le vide.</p>
                <a href="carte.php" class="btn-gold">DÉCOUVRIR LA CARTE</a>
            </div>

        <?php else: ?>

            <table class="panier-table">
                <thead>
                    <tr>
                        <th>PRODUIT</th>
                        <th>PRIX</th>
                        <th>QUANTITÉ</th>
                        <th class="text-right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['panier'] as $cle => $item):
                        $produit = null;
                        foreach ($tousLesProduits as $p) {
                            if ($p['id'] === $item['id']) { $produit = $p; break; }
                        }
                        if (!$produit) continue;

                            $prix = $produit['prix'] ?? $produit['prix_total'] ?? 0;
                            $sousTotal = $prix * $item['qte'];                        
                            $totalCommande += $sousTotal;
                    ?>
                    <tr>
                        <td>
                            <div class="prod-info">
                                <span class="prod-name"><?= htmlspecialchars($produit['nom']) ?></span>
                                <?php if (!empty($item['retraits'])): ?>
                                    <span class="prod-custom">SANS : <?= implode(', ', $item['retraits']) ?></span>
                                <?php endif; ?>
                                <a href="modifier_item.php?cle=<?= $cle ?>" class="btn-edit-custom">MODIFIER LA RECETTE</a>
                            </div>
                        </td>
                            <td><?= $prix ?>€</td>
                        <td>
                            <div class="qte-picker">
                                <a href="modifier_panier.php?id=<?= $cle ?>&action=moins" class="btn-qte">-</a>
                                <span><?= $item['qte'] ?></span>
                                <a href="modifier_panier.php?id=<?= $cle ?>&action=plus" class="btn-qte">+</a>
                            </div>
                        </td>
                        <td class="text-right">
                            <?= $sousTotal ?>€
                            <a href="modifier_panier.php?id=<?= $cle ?>&action=supprimer" class="btn-delete">✕</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="panier-footer">
    <div class="total-section">
        <span class="total-label">TOTAL COMMANDE</span>
        <span class="total-amount"><?= $totalCommande ?>€</span>
    </div>
    <div class="action-buttons">
        <a href="vider_panier.php" class="btn-outline" onclick="return confirm('Vider votre panier ?')">VIDER</a>
       
       
        <form action="paiement.php" method="POST" id="form-paiement">
            <input type="hidden" id="input-plan-type"  name="plan_type"  value="livraison">
            <input type="hidden" id="input-plan-date"  name="plan_date"  value="">
            <input type="hidden" id="input-plan-heure" name="plan_heure" value="">
            <button type="submit" class="btn-gold">PASSER AU RÈGLEMENT</button>
        </form>    
    
    
    </div>
</div>

<div class="planification-box">
    <div class="plan-header">
        <span class="plan-icon">🕐</span>
        <div>
            <div class="plan-titre">Planifier ma commande</div>
            <div class="plan-sous">Choisissez votre créneau de livraison ou de retrait</div>
        </div>
    </div>
    <div class="plan-body">
        <div class="plan-group">
            <label>Type</label>
            <div class="plan-toggle">
                <button class="plan-btn active" onclick="selectType(this, 'livraison')">LIVRAISON</button>
                <button class="plan-btn" onclick="selectType(this, 'sur_place')">SUR PLACE</button>
            </div>
            <input type="hidden" id="plan-type" value="livraison">
        </div>
        <div class="plan-group">
            <label>Date</label>
            <input type="date" id="plan-date" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="plan-group">
            <label>Heure</label>
            <select id="plan-heure">
                <option value="">— Choisir un créneau —</option>
                <?php
                $debut = strtotime('19:00');
                $fin   = strtotime('22:30');
                for ($t = $debut; $t <= $fin; $t += 30 * 60) {
                    echo '<option value="' . date('H:i', $t) . '">' . date('H:i', $t) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="plan-confirm">
            <span id="plan-resume"></span>
            <button class="btn-gold" onclick="confirmerPlanification()">CONFIRMER LE CRÉNEAU</button>
        </div>
    </div>
</div>

        <?php endif; ?>
    </div>

<?php endif; ?>





<script>
        function selectType(btn, type) {
            document.querySelectorAll('.plan-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('plan-type').value = type;
            updateResume();
        }

        function updateResume() {
            const type  = document.getElementById('plan-type')?.value;
            const date  = document.getElementById('plan-date')?.value;
            const heure = document.getElementById('plan-heure')?.value;
            const resume = document.getElementById('plan-resume');
            if (!resume) return;
            if (date && heure) {
                const d = new Date(date);
                const jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
                const label = type === 'livraison' ? 'Livraison' : 'Sur place';
                resume.textContent = `${label} — ${jours[d.getDay()]} ${d.getDate()}/${d.getMonth()+1} à ${heure}`;
            } else {
                resume.textContent = '';
            }
        }

        function confirmerPlanification() {
            const type  = document.getElementById('plan-type')?.value;
            const date  = document.getElementById('plan-date')?.value;
            const heure = document.getElementById('plan-heure')?.value;

            if (!date || !heure) {
                alert('Veuillez choisir une date et une heure.');
                return;
            }

            sessionStorage.setItem('planification', JSON.stringify({ type, date, heure }));

            const it = document.getElementById('input-plan-type');
            const id = document.getElementById('input-plan-date');
            const ih = document.getElementById('input-plan-heure');
            if (it) it.value = type;
            if (id) id.value = date;
            if (ih) ih.value = heure;

            const btn = document.querySelector('.plan-confirm .btn-gold');
            btn.textContent = '✓ CRÉNEAU CONFIRMÉ';
            btn.style.background = '#44cc88';
            btn.style.color = '#000';
            setTimeout(() => {
                btn.textContent = 'CONFIRMER LE CRÉNEAU';
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        }

        document.getElementById('plan-date')?.addEventListener('change', updateResume);
        document.getElementById('plan-heure')?.addEventListener('change', updateResume);

        const saved = sessionStorage.getItem('planification');
        if (saved) {
            const p = JSON.parse(saved);
            if (document.getElementById('plan-date'))  document.getElementById('plan-date').value  = p.date;
            if (document.getElementById('plan-heure')) document.getElementById('plan-heure').value = p.heure;
            document.getElementById('input-plan-type').value  = p.type  || 'livraison';
            document.getElementById('input-plan-date').value  = p.date  || '';
            document.getElementById('input-plan-heure').value = p.heure || '';
            updateResume();
        }
    </script>
</body>

</body>
</html>