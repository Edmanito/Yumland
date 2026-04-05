/* =========================================
   KAISEKI SHUNEI — COMMANDE.JS
   Logique du dashboard Kanban cuisine
   ========================================= */

function updateClock() {
    const now = new Date();
    const h = now.getHours().toString().padStart(2, '0');
    const m = now.getMinutes().toString().padStart(2, '0');
    document.getElementById('clock').textContent = h + ':' + m;
}
updateClock();
setInterval(updateClock, 1000);

function changerStatut(btn, nouveauStatut) {
    const card = btn.closest('.order-card');
    const colonne = colonnes[nouveauStatut];

    if (!colonne) return;

    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    card.style.transition = 'all 0.25s ease';

    setTimeout(() => {
        const nouvBtn = card.querySelector('.btn-action');
        nouvBtn.className = 'btn-action ' + colonne.btnClass;
        nouvBtn.textContent = colonne.btnLabel;
        nouvBtn.disabled = colonne.disabled || false;

        if (colonne.disabled) {
            nouvBtn.setAttribute('disabled', true);
        } else {
            nouvBtn.removeAttribute('disabled');
            nouvBtn.setAttribute('onclick', `changerStatut(this, '${colonne.next}')`);
        }

        colonne.liste.appendChild(card);

        const urgentTag = card.querySelector('.urgent-tag');
        if (urgentTag && nouveauStatut !== 'cuisine') urgentTag.remove();
        if (nouveauStatut !== 'cuisine') card.classList.remove('urgent');

        card.style.opacity = '0';
        card.style.transform = 'translateY(8px)';
        requestAnimationFrame(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        });

        updateCompteurs();
    }, 250);
}

const colonnes = {
    cuisine: {
        liste: null, 
        btnClass: 'success',
        btnLabel: 'Marquer prêt',
        next: 'pret'
    },
    pret: {
        liste: null,
        btnClass: 'gold',
        btnLabel: 'Assigner livreur',
        next: 'livraison'
    },
    livraison: {
        liste: null,
        btnClass: 'muted',
        btnLabel: 'En route...',
        disabled: true,
        next: null
    }
};

function updateCompteurs() {
    const cols = document.querySelectorAll('.kanban-col');

    cols.forEach(col => {
        const cards = col.querySelectorAll('.order-card').length;
        const compteur = col.querySelector('.col-count');
        if (compteur) compteur.textContent = cards;
    });

    const statPills = document.querySelectorAll('.stat-pill .stat-num');
    const allCols = document.querySelectorAll('.kanban-col .cards-list');

    if (allCols.length >= 4 && statPills.length >= 4) {
        statPills[0].textContent = allCols[0].querySelectorAll('.order-card').length;
        statPills[1].textContent = allCols[1].querySelectorAll('.order-card').length;
        statPills[2].textContent = allCols[2].querySelectorAll('.order-card').length;
        statPills[3].textContent = allCols[3].querySelectorAll('.order-card').length;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const listes = document.querySelectorAll('.cards-list');

    if (listes.length >= 4) {
        colonnes.cuisine.liste  = listes[1];
        colonnes.pret.liste     = listes[2];
        colonnes.livraison.liste = listes[3];
    }

    updateCompteurs();
});