document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');
    const blockButtons = document.querySelectorAll('.block-toggle');

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

    blockButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); 
            
            const row = e.target.closest('.user-row');
            row.classList.toggle('blocked');
            
            if(row.classList.contains('blocked')) {
                btn.textContent = '✅';
                btn.title = 'Débloquer';
            } else {
                btn.textContent = '🚫';
                btn.title = 'Bloquer';
            }
        });
    });

    rows.forEach(row => {
        row.style.cursor = 'pointer'; 
        row.addEventListener('click', (e) => {
            if (e.target.closest('.btn-group') || e.target.tagName === 'A') {
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