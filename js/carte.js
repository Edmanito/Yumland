document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('menuSearch');
    const cards = document.querySelectorAll('.dish-card');
    const modal = document.getElementById('imageZoom');
    const modalImg = document.getElementById('imgFull');
    const caption = document.getElementById('caption');
    const closeBtn = document.querySelector('.close-zoom');

    // Filtrage dynamique
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase();
        
        cards.forEach(card => {
            const title = card.getAttribute('data-title').toLowerCase();
            const desc = card.querySelector('.dish-desc').innerText.toLowerCase();
            card.style.display = (title.includes(query) || desc.includes(query)) ? '' : 'none';
        });
    });

    // Zoom Image
    cards.forEach(card => {
        card.addEventListener('click', () => {
            modal.style.display = 'flex';
            modalImg.src = card.getAttribute('data-img');
            caption.innerText = card.getAttribute('data-title');
            document.body.style.overflow = 'hidden';
        });
    });

    // Fermeture
    const close = () => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };
    closeBtn.onclick = close;
    modal.onclick = (e) => { if(e.target === modal) close(); };
});