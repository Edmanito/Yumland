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
    searchInput.addEventListener('keyup', filterUsers);
    roleFilter.addEventListener('change', filterUsers);

    // 3. Gestion du blocage (Interaction visuelle)
    blockButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('.user-row');
            row.classList.toggle('blocked');
            
            // On change l'icône selon l'état
            if(row.classList.contains('blocked')) {
                btn.textContent = '✅';
                btn.title = 'Débloquer';
            } else {
                btn.textContent = '🚫';
                btn.title = 'Bloquer';
            }
        });
    });
});