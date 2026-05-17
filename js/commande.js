/* =========================================
   KAISEKI SHUNEI — COMMANDE.JS
   Logique Kanban avec mouvement fluide (Zéro PHP inline)
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // --- 0. L'Horloge en temps réel ---
    function updateClock() {
        const now = new Date();
        const clockEl = document.getElementById('clock');
        if (clockEl) {
            clockEl.textContent =
                now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- 1. Répcupération des données HTML (Livreurs) ---
    const kanbanBoard = document.getElementById('kanban-board');
    let LIVREURS_ACTIFS = [];
    if (kanbanBoard) {
        try {
            LIVREURS_ACTIFS = JSON.parse(kanbanBoard.getAttribute('data-livreurs') || '[]');
        } catch (e) {
            console.error("Erreur de lecture des livreurs", e);
        }
    }

    // --- 2. Configuration des colonnes ---
    const listes = document.querySelectorAll('.cards-list');
    // Index des colonnes : 0:Attente, 1:Cuisine, 2:Prêtes, 3:Livraison
    const colonnes = {
        'en_preparation': { list: listes[1], btn: 'Marquer prêt', cls: 'success', next: 'pret' },
        'pret':           { list: listes[2], btn: 'Confier & Livrer', cls: 'gold', next: 'en_livraison' },
        'en_livraison':   { list: listes[3], btn: 'En route...', cls: 'muted', next: null }
    };

    function updateCompteurs() {
        document.querySelectorAll('.kanban-col').forEach((col, idx) => {
            const count = col.querySelectorAll('.order-card').length;
            col.querySelector('.col-count').textContent = count;
            const pill = document.querySelectorAll('.stat-pill .stat-num')[idx];
            if(pill) pill.textContent = count;
        });
    }

    // --- 3. GESTION DU MOUVEMENT DES CARTES (Changement de statut) ---
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-change-statut');
        if (!btn) return;

        e.preventDefault();
        const url = btn.getAttribute('href');
        const params = new URLSearchParams(url.split('?')[1]);
        const id = params.get('id');
        const nextStatut = params.get('statut');

        btn.style.opacity = '0.5';
        btn.style.pointerEvents = 'none';

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                const card = btn.closest('.order-card');
                const config = colonnes[nextStatut];

                if (config) {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';

                    setTimeout(() => {
                        const actionArea = card.querySelector('.card-actions');
                        
                        if (nextStatut === 'pret') {
                            // On reconstruit le menu déroulant avec les données récupérées en HTML
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
                            btn.textContent = config.btn;
                            btn.className = `btn-action btn-change-statut ${config.cls}`;
                            btn.setAttribute('href', `../actions/statut_commande.php?id=${id}&statut=${config.next}`);
                            btn.style.opacity = '1';
                            btn.style.pointerEvents = 'auto';
                        }

                        config.list.prepend(card);
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                        updateCompteurs();
                    }, 300);
                }
            }
        } catch (err) {
            window.location.reload(); 
        }
    });

    // --- 4. GESTION DE L'ASSIGNATION LIVREUR ---
    document.addEventListener('submit', async (e) => {
        if (e.target.matches('.form-assign-livreur')) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button');
            const livreurNom = form.querySelector('select option:checked').text;

            btn.disabled = true;
            btn.textContent = 'Assignation...';

            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
                const data = await response.json();

                if (data.success) {
                    const card = form.closest('.order-card');
                    card.style.opacity = '0';

                    setTimeout(() => {
                        const actionArea = card.querySelector('.card-actions');
                        actionArea.innerHTML = `<button class="btn-action muted" disabled style="width:100%; opacity:0.5;">En route...</button>`;
                        
                        const itemsArea = card.querySelector('.card-items');
                        const infoLivreur = document.createElement('div');
                        infoLivreur.className = 'livreur-info';
                        infoLivreur.style.marginTop = '10px';
                        infoLivreur.innerHTML = `<span>🛵</span> <span style="font-size:0.9rem;">${livreurNom}</span>`;
                        itemsArea.appendChild(infoLivreur);

                        colonnes['en_livraison'].list.prepend(card);
                        card.style.opacity = '1';
                        updateCompteurs();
                    }, 300);
                }
            } catch (err) { window.location.reload(); }
        }
    });

    // --- 5. SMART REFRESH ---
    setInterval(() => {
        const isEditing = document.querySelector('select:focus');
        if (!isEditing) window.location.reload();
    }, 15000);

});