/* =========================================
   KAISEKI SHUNEI — PROFIL.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // =========================================================
    // 1. GESTION DE LA MODALE D'ÉDITION (PROFIL)
    // =========================================================
    const btnEdit = document.getElementById('btn-edit-profile');
    const modal = document.getElementById('edit-profile-modal');
    const btnClose = document.getElementById('close-edit-modal');

    if (btnEdit && modal && btnClose) {
        btnEdit.addEventListener('click', () => {
            modal.classList.add('active');
            if (emailInput) updateCounter(emailInput, counterEmail);
            if (mdpInput) updateCounter(mdpInput, counterMdp);
        });

        btnClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    // =========================================================
    // 2. GESTION DU FORMULAIRE ET DES COMPTEURS (PROFIL)
    // =========================================================
    const formEdit = document.getElementById('form-edit-profile');
    
    // Variables déclarées globalement pour ce bloc
    let emailInput, mdpInput, telInput, btnSubmit, counterEmail, counterMdp;

    if (formEdit) {
        emailInput = formEdit.querySelector('input[name="login"]');
        mdpInput = formEdit.querySelector('input[name="mdp"]');
        telInput = formEdit.querySelector('input[name="telephone"]');
        btnSubmit = formEdit.querySelector('.btn-submit');

        const togglePassword = document.getElementById('toggleEditPassword');
        if (togglePassword && mdpInput) {
            togglePassword.addEventListener('click', () => {
                const type = mdpInput.getAttribute('type') === 'password' ? 'text' : 'password';
                mdpInput.setAttribute('type', type);
                togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }

        counterEmail = document.getElementById('counter-edit-email');
        counterMdp = document.getElementById('counter-edit-mdp');

        function updateCounter(inputElement, counterElement) {
            if (!inputElement || !counterElement) return;
            const currentLength = inputElement.value.length;
            const maxLength = inputElement.getAttribute('maxlength');
            counterElement.textContent = `${currentLength} / ${maxLength}`;
            
            if (currentLength >= maxLength) {
                counterElement.style.color = '#ff4444';
            } else {
                counterElement.style.color = '#888';
            }
        }

        if (emailInput) emailInput.addEventListener('input', () => updateCounter(emailInput, counterEmail));
        if (mdpInput) mdpInput.addEventListener('input', () => updateCounter(mdpInput, counterMdp));

        if (telInput) {
            telInput.addEventListener('input', () => {
                let val = telInput.value.replace(/\D/g, '').substring(0, 10);
                if (val.length > 0) {
                    val = val.match(/.{1,2}/g).join(' ');
                }
                telInput.value = val;
            });
        }

        // ENVOI DU FORMULAIRE PROFIL
        formEdit.addEventListener('submit', (e) => {
            let isValid = true;
            
            emailInput.style.outline = 'none';
            telInput.style.outline = 'none';
            mdpInput.style.outline = 'none';

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                isValid = false;
                emailInput.style.outline = '1px solid #ff4444';
            }

            const phoneRegex = /^(\+33|0)[1-9](\s?\d{2}){4}$/;
            if (!phoneRegex.test(telInput.value.replace(/\s/g, ''))) {
                isValid = false;
                telInput.style.outline = '1px solid #ff4444';
            }

            if (mdpInput.value.length > 0 && mdpInput.value.length < 8) {
                isValid = false;
                mdpInput.style.outline = '1px solid #ff4444';
            }

            if (!isValid) {
                e.preventDefault();
                btnSubmit.style.animation = 'shake 0.4s';
                setTimeout(() => btnSubmit.style.animation = '', 400);
            } else {
                e.preventDefault(); 
                
                // FormData va capturer automatiquement Adresse, Étage et Interphone !
                const formData = new FormData(formEdit);
                
                btnSubmit.textContent = 'ENREGISTREMENT...';
                btnSubmit.style.opacity = '0.7';
                btnSubmit.style.pointerEvents = 'none';

                fetch('../actions/modifier_profil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // LE BUG EST CORRIGÉ ICI : On valide juste le profil et on recharge la page
                        btnSubmit.style.background = '#4caf50';
                        btnSubmit.style.color = 'white';
                        btnSubmit.textContent = 'PROFIL MIS À JOUR ✓';
                        
                        setTimeout(() => {
                            location.reload(); 
                        }, 800);
                    } else {
                        alert('Erreur : ' + data.message);
                        btnSubmit.textContent = 'ENREGISTRER';
                        btnSubmit.style.opacity = '1';
                        btnSubmit.style.pointerEvents = 'auto';
                    }
                })
                .catch(error => {
                    console.error('Erreur Fetch:', error);
                    alert('Une erreur est survenue lors de la communication avec le serveur.');
                    btnSubmit.textContent = 'ENREGISTRER';
                    btnSubmit.style.opacity = '1';
                    btnSubmit.style.pointerEvents = 'auto';
                });
            }
        });
    }

    // =========================================================
    // 3. GESTION DE LA MODIFICATION DE COMMANDE (AJOUT PHASE 3)
    // =========================================================
    const modalCmd = document.getElementById('edit-cmd-modal');
    const closeCmdBtn = document.getElementById('close-cmd-modal');
    const btnsModifierCmd = document.querySelectorAll('.btn-modifier-cmd');
    const btnSaveCmd = document.getElementById('btn-save-cmd');
    
    let currentCmdId = null;
    let oldTotal = 0;
    let currentArticles = [];

    if (btnsModifierCmd.length > 0) {
        btnsModifierCmd.forEach(btn => {
            btn.addEventListener('click', () => {
                currentCmdId = btn.getAttribute('data-id');
                oldTotal = parseFloat(btn.getAttribute('data-prix'));
                currentArticles = JSON.parse(btn.getAttribute('data-articles'));
                
                document.getElementById('modal-cmd-id').textContent = currentCmdId;
                document.getElementById('modal-old-price').textContent = oldTotal;
                
                renderModalItems();
                
                if (modalCmd) modalCmd.classList.add('active');
            });
        });
    }

    if (closeCmdBtn) {
        closeCmdBtn.addEventListener('click', () => {
            modalCmd.classList.remove('active');
        });
    }

    function renderModalItems() {
        const container = document.getElementById('modal-cmd-items');
        if (!container) return;
        
        container.innerHTML = '';
        let newTotal = 0;

        currentArticles.forEach((art, index) => {
            if (art.quantite <= 0) return; 

            newTotal += art.quantite * art.prix_unitaire;

            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.justifyContent = 'space-between';
            div.style.alignItems = 'center';
            div.style.padding = '8px 0';
            
            div.innerHTML = `
                <div style="flex:1;">
                    <span style="color:#e8e2d9;">${art.nom}</span><br>
                    <span style="color:#888; font-size:0.8rem;">${art.prix_unitaire}€ / u</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <button type="button" class="btn-qty-minus" data-index="${index}" style="background:#333; color:white; border:none; width:25px; height:25px; cursor:pointer;">-</button>
                    <span style="color:#bc9c64; font-weight:bold; width:20px; text-align:center;">${art.quantite}</span>
                    <button type="button" class="btn-qty-plus" data-index="${index}" style="background:#333; color:white; border:none; width:25px; height:25px; cursor:pointer;">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('modal-new-price').textContent = newTotal;
        gererAffichageDifference(newTotal);

        document.querySelectorAll('.btn-qty-minus').forEach(b => b.addEventListener('click', (e) => updateQty(e, -1)));
        document.querySelectorAll('.btn-qty-plus').forEach(b => b.addEventListener('click', (e) => updateQty(e, 1)));
    }

    function updateQty(e, delta) {
        const index = e.target.getAttribute('data-index');
        currentArticles[index].quantite += delta;
        
        if (currentArticles[index].quantite < 0) {
            currentArticles[index].quantite = 0; 
        }
        renderModalItems();
    }

    function gererAffichageDifference(newTotal) {
        const msgDiv = document.getElementById('modal-diff-msg');
        if (!msgDiv) return;

        const diff = newTotal - oldTotal;

        if (diff > 0) {
            msgDiv.style.display = 'block';
            msgDiv.style.background = 'rgba(245, 158, 11, 0.1)';
            msgDiv.style.color = '#f59e0b';
            msgDiv.innerHTML = `⚠️ <b>Supplément de ${diff}€</b>. Vous devrez régler cette différence pour que la cuisine démarre.`;
        } else if (diff < 0) {
            msgDiv.style.display = 'block';
            msgDiv.style.background = 'rgba(34, 197, 94, 0.1)';
            msgDiv.style.color = '#22c55e';
            msgDiv.innerHTML = `🎁 <b>Économie de ${Math.abs(diff)}€</b>. Un ticket de réduction de ce montant sera ajouté à votre profil.`;
        } else {
            msgDiv.style.display = 'none';
        }
    }

    if (btnSaveCmd) {
        btnSaveCmd.addEventListener('click', async () => {
            btnSaveCmd.textContent = "TRAITEMENT...";
            btnSaveCmd.style.opacity = '0.7';
            btnSaveCmd.style.pointerEvents = 'none';
            
            const articlesFinaux = currentArticles.filter(a => a.quantite > 0);

            try {
                const response = await fetch('../actions/edit_cmd.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_commande: currentCmdId,
                        articles: articlesFinaux
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    btnSaveCmd.style.background = '#4caf50';
                    btnSaveCmd.style.color = 'white';
                    btnSaveCmd.textContent = 'COMMANDE MODIFIÉE ✓';
                    
                    let nouveauTotalFinal = 0;
                    articlesFinaux.forEach(a => nouveauTotalFinal += a.quantite * a.prix_unitaire);
                    const diff = nouveauTotalFinal - oldTotal;

                    setTimeout(() => {
                        if (diff > 0) {
                            window.location.href = `paiement.php?cmd=${currentCmdId}&montant=${diff}`;
                        } else {
                            location.reload(); 
                        }
                    }, 1500);
                } else {
                    alert("Erreur : " + data.message);
                    resetSaveBtn();
                }
            } catch (err) {
                console.error(err);
                alert("Erreur de connexion avec le serveur.");
                resetSaveBtn();
            }
        });
    }

    function resetSaveBtn() {
        btnSaveCmd.textContent = "VALIDER LES MODIFICATIONS";
        btnSaveCmd.style.opacity = '1';
        btnSaveCmd.style.pointerEvents = 'auto';
        btnSaveCmd.style.background = '#bc9c64';
        btnSaveCmd.style.color = 'black';
    }

    // =========================================================
    // 4. GESTION DE LA VISUALISATION DE L'AVIS (L'OEIL)
    // =========================================================
    const modalAvis = document.getElementById('modal-view-avis');
    const contentAvis = document.getElementById('content-avis-popup');
    const btnCloseAvis = document.getElementById('close-avis-modal');
    const btnCloseAvisPop = document.getElementById('btn-close-avis-popup');

    const btnsViewAvis = document.querySelectorAll('.btn-view-avis');
    if (btnsViewAvis.length > 0) {
        btnsViewAvis.forEach(btn => {
            btn.addEventListener('click', () => {
                try {
                    const data = JSON.parse(btn.getAttribute('data-avis'));
                    
                    if (contentAvis) {
                        contentAvis.innerHTML = `
                            <div style="margin-bottom:20px;">
                                <p style="color:#bc9c64; text-transform:uppercase; font-size:0.7rem; letter-spacing:1px;">Notes attribuées</p>
                                <p style="font-size:1.1rem; margin:10px 0;">Cuisine : <span style="color:#bc9c64;">${"★".repeat(data.produits)}${"☆".repeat(5-data.produits)}</span></p>
                                <p style="font-size:1.1rem; margin:10px 0;">Service : <span style="color:#bc9c64;">${"★".repeat(data.livraison)}${"☆".repeat(5-data.livraison)}</span></p>
                            </div>
                            <div style="border-top: 1px solid #333; padding-top:20px;">
                                <p style="color:#bc9c64; text-transform:uppercase; font-size:0.7rem; letter-spacing:1px;">Commentaire</p>
                                <p style="font-style:italic; font-size:0.95rem; line-height:1.6; margin-top:10px; color:#ddd;">
                                    "${data.commentaire || 'Aucun commentaire laissé.'}"
                                </p>
                            </div>
                            <p style="font-size:0.65rem; color:#666; margin-top:25px;">Évalué le ${data.date_note ? data.date_note : 'Date inconnue'}</p>
                        `;
                    }
                    
                    if (modalAvis) {
                        modalAvis.classList.add('active'); 
                        modalAvis.style.display = 'flex';  
                    }
                } catch (e) {
                    console.error("Erreur de format JSON sur l'avis:", e);
                    alert("Impossible d'afficher l'avis. Le format est incorrect.");
                }
            });
        });
    }

    const fermerAvis = () => {
        if (modalAvis) {
            modalAvis.classList.remove('active');
            setTimeout(() => {
                modalAvis.style.display = 'none';
            }, 300);
        }
    };
    
    if (btnCloseAvis) btnCloseAvis.addEventListener('click', fermerAvis);
    if (btnCloseAvisPop) btnCloseAvisPop.addEventListener('click', fermerAvis);

});