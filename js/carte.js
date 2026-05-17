/* =========================================
   KAISEKI SHUNEI — CARTE.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    const searchInput = document.getElementById('menuSearch');
    const regimeSelect = document.getElementById('filter-regime');
    const saveurSelect = document.getElementById('filter-saveur');
    const sortSelect = document.getElementById('sort-price');
    const sectionsContainer = document.querySelector('.carte-main');
    
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    const modalCaption = document.getElementById('modalCaption');
    const modalClose = document.querySelector('.modal-close');

    let currentPlats = []; 

    // --- OBSERVATEURS ---
    const catBtns = document.querySelectorAll('.cat-nav-btn');
    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                catBtns.forEach(btn => {
                    btn.classList.toggle('active', btn.getAttribute('data-target') === id);
                });
            }
        });
    }, { threshold: 0.3 });

    const animObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationDelay = '0s';
                entry.target.classList.add('visible');
                animObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.cat-section, .menus-section').forEach(s => sectionObserver.observe(s));
    
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

    // --- MODALE IMAGE ---
    const closeModal = () => {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

    function attacherEvenementsCartes() {
        const cards = document.querySelectorAll('.dish-card, .drink-card, .menu-card');
        cards.forEach(card => {
            animObserver.observe(card);
            if (card.hasAttribute('data-img')) {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.btn-ajouter, .drink-btn, .btn-menu')) return; 
                    const img = card.getAttribute('data-img');
                    const name = card.getAttribute('data-title');
                    if (img && modal) {
                        modalImg.src = img;
                        modalCaption.textContent = name;
                        modal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                });
            }
        });
    }
    attacherEvenementsCartes();

    // --- LOGIQUE FILTRE AJAX ---
    async function chargerPlats() {
        if (!searchInput || !regimeSelect || !saveurSelect) return;

        const query = searchInput.value.trim().toLowerCase();
        const regime = regimeSelect.value;
        const saveur = saveurSelect.value;

        const url = `../actions/filtrer_carte.php?recherche=${encodeURIComponent(query)}&regime=${regime}&saveur=${saveur}`;

        try {
            const response = await fetch(url);
            const texteBrut = await response.text();
            
            // Extraction sécurisée du JSON pour éviter les bugs PHP invisibles
            const indexDebut = texteBrut.indexOf('[');
            const indexFin = texteBrut.lastIndexOf(']') + 1;

            if (indexDebut !== -1 && indexFin !== -1) {
                const jsonPropre = texteBrut.substring(indexDebut, indexFin);
                currentPlats = JSON.parse(jsonPropre);
            } else {
                console.error("Erreur de format JSON:", texteBrut);
                currentPlats = [];
            }
            
            appliquerTri(); 
            renderPlats();  
        } catch (error) {
            console.error("Erreur Fetch:", error);
        }
    }

    function appliquerTri() {
        if (!sortSelect || currentPlats.length === 0) return;
        const order = sortSelect.value;
        if (order === 'asc') currentPlats.sort((a, b) => a.prix - b.prix);
        else if (order === 'desc') currentPlats.sort((a, b) => b.prix - a.prix);
    }

    function renderPlats() {
        if (!sectionsContainer) return;

        if (currentPlats.length === 0) {
            sectionsContainer.innerHTML = '<div style="text-align:center; padding: 50px; color:#bc9c64; font-size:1.2rem;">Aucun plat ne correspond à vos critères.</div>';
            return;
        }

        sectionsContainer.innerHTML = `
            <section class="cat-section" style="padding-top: 40px;">
                <div class="cat-header">
                    <div class="cat-title-block">
                        <h2>Résultats de la recherche</h2>
                        <p>NOTRE SÉLECTION</p>
                    </div>
                </div>
                <div class="dishes-grid layout-2" id="grid-resultats"></div>
            </section>
        `;

        const grid = document.getElementById('grid-resultats');

        currentPlats.forEach(p => {
            const src = p.image ? `../${p.image}` : '';
            const isPremium = p.prix >= 60;
            
            let allergenesHTML = '';
            if (p.allergenes && p.allergenes.length > 0) {
                allergenesHTML = '<div class="dish-allergenes">';
                p.allergenes.forEach(a => {
                    allergenesHTML += `<span class="allergen-tag">${a}</span>`;
                });
                allergenesHTML += '</div>';
            }

            let imageHTML = src ? `
                <div class="dish-img-wrap">
                    <img src="${src}" alt="${p.nom}" loading="lazy">
                    <div class="dish-overlay"></div>
                    ${isPremium ? '<span class="dish-badge">SIGNATURE</span>' : ''}
                </div>
            ` : `
                <div class="dish-no-img"><span class="placeholder-kanji">料理</span></div>
            `;

            const card = `
                <article class="dish-card" data-title="${p.nom}" data-img="${src}">
                    ${imageHTML}
                    <div class="dish-body">
                        <div class="dish-top">
                            <h3 class="dish-name">${p.nom}</h3>
                            <span class="dish-price">${p.prix}€</span>
                        </div>
                        <p class="dish-desc">${p.description}</p>
                        ${allergenesHTML}
                        <a href="../actions/ajouter_panier.php?id=${p.id}" class="btn-ajouter">AJOUTER AU PANIER</a>
                    </div>
                </article>
            `;
            grid.innerHTML += card;
        });

        attacherEvenementsCartes();
    }

    if (searchInput) searchInput.addEventListener('input', chargerPlats);
    if (regimeSelect) regimeSelect.addEventListener('change', chargerPlats);
    if (saveurSelect) saveurSelect.addEventListener('change', chargerPlats);
    
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            if (currentPlats.length === 0) chargerPlats();
            else { appliquerTri(); renderPlats(); }
        });
    }

    // --- AJOUT PANIER AJAX ---
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-ajouter, .drink-btn, .btn-menu');
        if (btn) {
            e.preventDefault(); 
            const url = btn.getAttribute('href'); 
            const texteOriginal = btn.textContent;

            btn.textContent = 'AJOUT...';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.7';

            try {
                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    btn.textContent = 'AJOUTÉ ✓';
                    btn.style.background = '#4caf50';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#4caf50';
                    btn.style.opacity = '1';

                    let cartBtn = document.querySelector('.btn-cart');
                    if (cartBtn) {
                        let cartBadge = cartBtn.querySelector('.cart-count');
                        if (!cartBadge) cartBtn.innerHTML = `MON PANIER <span class="cart-count">${data.total_items}</span>`;
                        else cartBadge.textContent = data.total_items;
                        
                        const newBadge = document.querySelector('.cart-count');
                        if (newBadge) {
                            newBadge.style.transform = 'scale(1.4)';
                            newBadge.style.transition = 'transform 0.2s';
                            setTimeout(() => newBadge.style.transform = 'scale(1)', 200);
                        }
                    }
                    setTimeout(() => {
                        btn.textContent = texteOriginal;
                        btn.style.background = '';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                        btn.style.pointerEvents = 'auto';
                    }, 1500);
                } else {
                    alert("Erreur lors de l'ajout.");
                    btn.textContent = texteOriginal;
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                }
            } catch (error) {
                console.error("Erreur AJAX Panier:", error);
                btn.textContent = texteOriginal;
                btn.style.pointerEvents = 'auto';
                btn.style.opacity = '1';
            }
        }
    });

});