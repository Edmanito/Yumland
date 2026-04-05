document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');
    const blockButtons = document.querySelectorAll('.block-toggle');

    // 1. Fonction de filtrage
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

    // 2. Écouteurs d'événements pour les filtres
    if (searchInput) searchInput.addEventListener('keyup', filterUsers);
    if (roleFilter) roleFilter.addEventListener('change', filterUsers);

    // 3. Gestion du blocage (Interaction visuelle)
    blockButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Empêche la propagation pour ne pas déclencher le clic sur la ligne (redirection)
            e.stopPropagation(); 
            
            const row = e.target.closest('.user-row');
            row.classList.toggle('blocked');
            
            // Changement de l'icône selon l'état
            if(row.classList.contains('blocked')) {
                btn.textContent = '✅';
                btn.title = 'Débloquer';
            } else {
                btn.textContent = '🚫';
                btn.title = 'Bloquer';
            }
        });
    });

    // 4. Redirection vers le profil au clic sur la ligne (Consigne Admin)
    rows.forEach(row => {
        row.style.cursor = 'pointer'; // Curseur pointer pour indiquer que c'est cliquable

        row.addEventListener('click', (e) => {
            // Si on clique sur un bouton dans .btn-group ou un lien direct, on ne redirige pas ici
            if (e.target.closest('.btn-group') || e.target.tagName === 'A') {
                return;
            }

            // Récupération de l'ID utilisateur via la classe .user-id
            const userIdElement = row.querySelector('.user-id');
            if (userIdElement) {
                const userId = userIdElement.textContent.trim();
                // Redirection vers le profil
                window.location.href = `profil.php?id=${userId}`;
            }
        });
    });
});