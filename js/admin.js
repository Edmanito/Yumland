/* =========================================
   KAISEKI SHUNEI — ADMIN.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. FILTRAGE DES UTILISATEURS (Recherche & Rôle) ---
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');

    const filterUsers = () => {
        const searchValue = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;

        rows.forEach(row => {
            const userName = row.querySelector('.user-name').textContent.toLowerCase();
            const userEmail = row.querySelector('.email-cell').textContent.toLowerCase();
            const userRole = row.getAttribute('data-role');

            const matchesSearch = userName.includes(searchValue) || userEmail.includes(searchValue);
            const matchesRole = selectedRole === 'all' || userRole === selectedRole;

            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    };

    if (searchInput) searchInput.addEventListener('keyup', filterUsers);
    if (roleFilter) roleFilter.addEventListener('change', filterUsers);

    // --- 2. REDIRECTION AU CLIC SUR LA LIGNE ---
    rows.forEach(row => {
        row.style.cursor = 'pointer'; 
        row.addEventListener('click', (e) => {
            // Si on clique sur le bouton de blocage ou un lien, on ne redirige pas
            if (e.target.closest('.btn-toggle-status') || e.target.closest('.btn-group') || e.target.tagName === 'A') {
                return;
            }

            const userIdElement = row.querySelector('.user-id');
            if (userIdElement) {
                const userId = userIdElement.textContent.trim();
                window.location.href = `profil.php?id=${userId}`;
            }
        });
    });
});

/* =========================================
   3. LOGIQUE DE BLOCAGE VIA AJAX (VERSION EMOJIS)
   ========================================= */
document.addEventListener('click', async (e) => {
    // On cible le bouton qui a la classe btn-toggle-status
    const btn = e.target.closest('.btn-toggle-status');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation(); // Empêche la redirection vers le profil

    const url = btn.getAttribute('href');
    const row = btn.closest('.user-row');
    
    // Feedback visuel pendant l'attente
    btn.style.opacity = '0.5';
    btn.style.pointerEvents = 'none';

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
            const statusCell = row ? row.querySelector('.status-tag') : null;

            if (data.new_status === 'suspendu') {
                // État Bloqué : On affiche le bouton pour débloquer
                btn.textContent = '✅'; 
                btn.title = 'Débloquer';
                if(row) row.classList.add('blocked-row');
                
                // Mise à jour de l'étiquette de statut dans le tableau
                if(statusCell) {
                    statusCell.className = 'status-tag suspended';
                    statusCell.innerHTML = '<span class="dot" style="background:#ff4d4d"></span> Suspendu';
                }
            } else {
                // État Actif : On affiche le bouton pour bloquer
                btn.textContent = '🚫'; 
                btn.title = 'Bloquer';
                if(row) row.classList.remove('blocked-row');
                
                // Mise à jour de l'étiquette de statut dans le tableau
                if(statusCell) {
                    statusCell.className = 'status-tag active';
                    statusCell.innerHTML = '<span class="dot"></span> Actif';
                }
            }
        } else {
            alert("Erreur : " + data.message);
        }
    } catch (err) {
        console.error("Erreur AJAX:", err);
        // Fallback : si l'AJAX échoue vraiment, on recharge la page
        window.location.reload();
    } finally {
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
    }
});