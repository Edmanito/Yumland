/* =========================================
   KAISEKI SHUNEI — CARTE.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // ── RECHERCHE ──
    const searchInput = document.getElementById('menuSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.dish-card, .drink-card, .menu-card');
            cards.forEach(card => {
                const name = (card.querySelector('.dish-name, .drink-name, h3')?.innerText || '').toLowerCase();
                const desc = (card.querySelector('.dish-desc, .drink-desc, p')?.innerText || '').toLowerCase();
                card.style.display = (!query || name.includes(query) || desc.includes(query)) ? '' : 'none';
            });
        });
    }

    // ── NAVIGATION CATÉGORIES ──
    const catBtns = document.querySelectorAll('.cat-nav-btn');
    catBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            const section = document.getElementById(target);
            if (section) {
                const offset = 80;
                const top = section.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // ── ACTIVE STATE NAV AU SCROLL ──
    const sections = document.querySelectorAll('.cat-section, .menus-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                catBtns.forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-target') === id);
                });
            }
        });
    }, { threshold: 0.3 });

    sections.forEach(s => observer.observe(s));

    // ── MODAL IMAGE ──
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const modalCaption = document.getElementById('modalCaption');
    const modalClose = document.querySelector('.modal-close');

    document.querySelectorAll('.dish-card[data-img]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.btn-ajouter')) return;
            const img = card.getAttribute('data-img');
            const name = card.getAttribute('data-title');
            if (img && modal) {
                modalImg.src = img;
                modalCaption.textContent = name;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    const closeModal = () => {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    // ── ANIMATION D'ENTRÉE ──
    const animObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = '0s';
                entry.target.classList.add('visible');
                animObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.dish-card, .drink-card, .menu-card').forEach(card => {
        animObserver.observe(card);
    });
});