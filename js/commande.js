/* =========================================
   KAISEKI SHUNEI — COMMANDE.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────
    // 0. HORLOGE EN TEMPS RÉEL
    // Affiche l'heure actuelle dans la topbar,
    // mise à jour chaque seconde via setInterval
    // ─────────────────────────────────────────
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('clock');
        if (clockEl) {
            clockEl.textContent =
                now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');
        }
    }
    setInterval(updateClock, 1000); // Mise à jour toutes les secondes
    updateClock(); // Appel immédiat pour éviter un délai d'1s au démarrage

    // ─────────────────────────────────────────
    // 1. RÉCUPÉRATION DES LIVREURS ACTIFS
    // Les livreurs sont passés depuis PHP via un attribut
    // data-livreurs sur le kanban (encodé en JSON).
    // On les récupère ici pour construire les menus déroulants
    // lors du déplacement d'une carte vers "Prêtes".
    // ─────────────────────────────────────────
    const kanbanBoard = document.getElementById('kanban-board');
    let LIVREURS_ACTIFS = [];
    if (kanbanBoard) {
        try {
            LIVREURS_ACTIFS = JSON.parse(kanbanBoard.getAttribute('data-livreurs') || '[]');
        } catch (e) {
            console.error("Erreur de lecture des livreurs", e);
        }
    }

    // ─────────────────────────────────────────
    // 2. CONFIGURATION DES COLONNES KANBAN
    // Chaque entrée correspond à un statut cible
    // et définit : la liste DOM cible, le label
    // du bouton, la classe CSS et le prochain statut.
    // Index des colonnes : 0=Attente, 1=Cuisine, 2=Prêtes, 3=Livraison
    // ─────────────────────────────────────────
    const listes = document.querySelectorAll('.cards-list');
    const colonnes = {
        'en_preparation': { list: listes[1], btn: 'Marquer prêt',   cls: 'success', next: 'pret' },
        'pret':           { list: listes[2], btn: 'Confier & Livrer', cls: 'gold',   next: 'en_livraison' },
        'en_livraison':   { list: listes[3], btn: 'En route...',     cls: 'muted',   next: null }
    };

    /**
     * Met à jour les compteurs affichés dans les en-têtes de colonnes
     * et dans les pills de statistiques en haut de page.
     */
    function updateCompteurs() {
        document.querySelectorAll('.kanban-col').forEach((col, idx) => {
            const count = col.querySelectorAll('.order-card').length;
            col.querySelector('.col-count').textContent = count;
            const pill = document.querySelectorAll('.stat-pill .stat-num')[idx];
            if (pill) pill.textContent = count;
        });
    }

    // ─────────────────────────────────────────
    // 3. CHANGEMENT DE STATUT D'UNE COMMANDE (AJAX)
    // Écoute les clics sur les boutons .btn-change-statut.
    // Au lieu de suivre le lien href (rechargement de page),
    // on envoie une requête fetch() à statut_commande.php
    // et on déplace la carte visuellement si succès.
    // ─────────────────────────────────────────
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-change-statut');
        if (!btn) return; // Clic sur autre chose → ignorer

        e.preventDefault(); // Empêche la navigation via le href

        // Extraction de l'ID et du statut depuis l'URL du bouton
        const url = btn.getAttribute('href');
        const params = new URLSearchParams(url.split('?')[1]);
        const id = params.get('id');
        const nextStatut = params.get('statut');

        // Désactivation visuelle du bouton pendant la requête
        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';

        try {
            const response = await fetch(url);
            const data = await response.json(); // Réponse JSON de statut_commande.php

            if (data.success) {
                const card = btn.closest('.order-card');
                const config = colonnes[nextStatut];

                if (config) {
                    // Animation de sortie de la carte
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';

                    setTimeout(() => {
                        const actionArea = card.querySelector('.card-actions');

                        if (nextStatut === 'pret') {
                            // Cas spécial : quand une commande est prête,
                            // on remplace le bouton par un menu de sélection de livreur
                            // Les livreurs disponibles viennent de LIVREURS_ACTIFS (récupérés au début)
                            let selectHTML = `<form action="../actions/assigner_livreur.php" method="POST" class="form-assign-livreur" style="width:100%;">
                                <input type="hidden" name="id_commande" value="${id}">
                                <select name="id_livreur" required style="width:100%; margin-bottom:8px; padding:8px; background:#111; color:#fff; border:1px solid #333; border-radius:4px;">
                                    <option value="">-- Choisir Livreur --</option>`;

                            LIVREURS_ACTIFS.forEach(l => {
                                selectHTML += `<option value="${l.id}">${l.nom}</option>`;
                            });

                            selectHTML += `</select>
                                <button type="submit" class="btn-action gold" style="width:100%; border:none; cursor:pointer;">Confier & Livrer</button>
                            </form>`;
                            actionArea.innerHTML = selectHTML;
                        } else {
                            // Pour les autres statuts : on met juste à jour le bouton
                            btn.textContent = config.btn;
                            btn.className = `btn-action btn-change-statut ${config.cls}`;
                            btn.setAttribute('href', `../actions/statut_commande.php?id=${id}&statut=${config.next}`);
                            btn.style.opacity = '1';
                            btn.style.pointerEvents = 'auto';
                        }

                        // Déplacement de la carte vers la bonne colonne
                        config.list.prepend(card); // prepend = en haut de la liste
                        // Animation d'entrée
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                        updateCompteurs(); // Mise à jour des chiffres
                    }, 300); // Délai pour laisser l'animation de sortie se terminer
                }
            }
        } catch (err) {
            // En cas d'erreur réseau : rechargement de la page pour rester cohérent
            window.location.reload();
        }
    });

    // ─────────────────────────────────────────
    // 4. ASSIGNATION D'UN LIVREUR (AJAX)
    // Soumission du formulaire de sélection de livreur
    // via fetch() pour éviter le rechargement de page.
    // Si succès, la carte passe dans la colonne "En livraison"
    // avec le nom du livreur affiché.
    // ─────────────────────────────────────────
    document.addEventListener('submit', async (e) => {
        if (e.target.matches('.form-assign-livreur')) {
            e.preventDefault(); // Empêche la soumission classique du formulaire

            const form = e.target;
            const btn = form.querySelector('button');
            // Récupère le nom du livreur sélectionné pour l'afficher sur la carte
            const livreurNom = form.querySelector('select option:checked').text;

            // Feedback visuel pendant l'envoi
            btn.disabled = true;
            btn.textContent = 'Assignation...';

            try {
                // Envoi du formulaire via fetch (méthode POST avec FormData)
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await response.json();

                if (data.success) {
                    const card = form.closest('.order-card');
                    card.style.opacity = '0'; // Animation de sortie

                    setTimeout(() => {
                        // Remplacement du formulaire par un bouton inactif "En route..."
                        const actionArea = card.querySelector('.card-actions');
                        actionArea.innerHTML = `<button class="btn-action muted" disabled style="width:100%; opacity:0.5;">En route...</button>`;

                        // Ajout du nom du livreur sur la carte
                        const itemsArea = card.querySelector('.card-items');
                        const infoLivreur = document.createElement('div');
                        infoLivreur.className = 'livreur-info';
                        infoLivreur.style.marginTop = '10px';
                        infoLivreur.innerHTML = `<span>🛵</span> <span style="font-size:0.9rem;">${livreurNom}</span>`;
                        itemsArea.appendChild(infoLivreur);

                        // Déplacement dans la colonne "En livraison"
                        colonnes['en_livraison'].list.prepend(card);
                        card.style.opacity = '1';
                        updateCompteurs();
                    }, 300);
                }
            } catch (err) {
                window.location.reload(); // Fallback en cas d'erreur réseau
            }
        }
    });

    // ─────────────────────────────────────────
    // 5. SMART REFRESH (Rechargement automatique)
    // Recharge la page toutes les 15 secondes pour
    // ─────────────────────────────────────────
    setInterval(() => {
        const isEditing = document.querySelector('select:focus');
        if (!isEditing) window.location.reload();
    }, 15000); // 15 000 ms = 15 secondes

});