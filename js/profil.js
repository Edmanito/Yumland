/* =========================================
   KAISEKI SHUNEI — PROFIL.JS
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // ═══════════════════════════════════════════
    // 1. MODALE D'ÉDITION DU PROFIL
    // Formulaire permettant de modifier :
    // email, mot de passe, téléphone, adresse
    // Soumis via fetch() vers modifier_profil.php
    // ═══════════════════════════════════════════

    const btnEdit  = document.getElementById('btn-edit-profile');
    const modal    = document.getElementById('edit-profile-modal');
    const btnClose = document.getElementById('close-edit-modal');

    if (btnEdit && modal && btnClose) {
        // Ouverture de la modale au clic sur le crayon ✎
        btnEdit.addEventListener('click', () => {
            modal.classList.add('active');
            // Mise à jour immédiate des compteurs de caractères à l'ouverture
            if (emailInput) updateCounter(emailInput, counterEmail);
            if (mdpInput)   updateCounter(mdpInput, counterMdp);
        });

        // Fermeture via le bouton ✕
        btnClose.addEventListener('click', () => modal.classList.remove('active'));

        // Fermeture en cliquant sur l'overlay (en dehors de la boîte)
        window.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }

    // ─────────────────────────────────────────
    // Déclaration des variables du formulaire
    // en dehors du bloc if pour qu'elles soient
    // accessibles dans les fonctions suivantes
    // ─────────────────────────────────────────
    const formEdit = document.getElementById('form-edit-profile');
    let emailInput, mdpInput, telInput, btnSubmit, counterEmail, counterMdp;

    if (formEdit) {
        emailInput  = formEdit.querySelector('input[name="login"]');
        mdpInput    = formEdit.querySelector('input[name="mdp"]');
        telInput    = formEdit.querySelector('input[name="telephone"]');
        btnSubmit   = formEdit.querySelector('.btn-submit');

        // Bouton œil pour afficher/masquer le mot de passe
        const togglePassword = document.getElementById('toggleEditPassword');
        if (togglePassword && mdpInput) {
            togglePassword.addEventListener('click', () => {
                const type = mdpInput.getAttribute('type') === 'password' ? 'text' : 'password';
                mdpInput.setAttribute('type', type);
                togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }

        counterEmail = document.getElementById('counter-edit-email');
        counterMdp   = document.getElementById('counter-edit-mdp');

        /**
         * Met à jour le compteur de caractères sous un champ.
         * Passe en rouge quand la limite max est atteinte.
         */
        function updateCounter(inputElement, counterElement) {
            if (!inputElement || !counterElement) return;
            const currentLength = inputElement.value.length;
            const maxLength = inputElement.getAttribute('maxlength');
            counterElement.textContent = `${currentLength} / ${maxLength}`;
            counterElement.style.color = currentLength >= maxLength ? '#ff4444' : '#888';
        }

        // Mise à jour des compteurs en temps réel
        if (emailInput) emailInput.addEventListener('input', () => updateCounter(emailInput, counterEmail));
        if (mdpInput)   mdpInput.addEventListener('input',   () => updateCounter(mdpInput, counterMdp));

        // Formatage automatique du téléphone : "0612345678" → "06 12 34 56 78"
        if (telInput) {
            telInput.addEventListener('input', () => {
                let val = telInput.value.replace(/\D/g, '').substring(0, 10); // Chiffres seulement, max 10
                if (val.length > 0) val = val.match(/.{1,2}/g).join(' ');    // Groupes de 2
                telInput.value = val;
            });
        }

        // ─────────────────────────────────────────
        // VALIDATION ET SOUMISSION DU FORMULAIRE PROFIL
        // Validation côté client avant envoi au serveur
        // ─────────────────────────────────────────
        formEdit.addEventListener('submit', (e) => {
            let isValid = true;

            // Reset des erreurs visuelles
            emailInput.style.outline = 'none';
            telInput.style.outline   = 'none';
            mdpInput.style.outline   = 'none';

            // Validation email via regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                isValid = false;
                emailInput.style.outline = '1px solid #ff4444';
            }

            // Validation téléphone français
            const phoneRegex = /^(\+33|0)[1-9](\s?\d{2}){4}$/;
            if (!phoneRegex.test(telInput.value.replace(/\s/g, ''))) {
                isValid = false;
                telInput.style.outline = '1px solid #ff4444';
            }

            // Si un mdp est saisi, il doit faire au moins 8 caractères
            if (mdpInput.value.length > 0 && mdpInput.value.length < 8) {
                isValid = false;
                mdpInput.style.outline = '1px solid #ff4444';
            }

            if (!isValid) {
                e.preventDefault(); // Annule la soumission
                // Animation de secousse sur le bouton pour indiquer l'erreur
                btnSubmit.style.animation = 'shake 0.4s';
                setTimeout(() => btnSubmit.style.animation = '', 400);
            } else {
                e.preventDefault(); // On gère manuellement avec fetch()

                const formData = new FormData(formEdit); // Capture tous les champs

                // Feedback visuel pendant l'envoi
                btnSubmit.textContent = 'ENREGISTREMENT...';
                btnSubmit.style.opacity = '0.7';
                btnSubmit.style.pointerEvents = 'none';

                // Envoi AJAX vers modifier_profil.php
                fetch('../actions/modifier_profil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Succès : feedback vert puis rechargement pour afficher les nouvelles infos
                        btnSubmit.style.background = '#4caf50';
                        btnSubmit.style.color = 'white';
                        btnSubmit.textContent = 'PROFIL MIS À JOUR ✓';
                        setTimeout(() => location.reload(), 800);
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

    // ═══════════════════════════════════════════
    // 2. MODIFICATION D'UNE COMMANDE EN ATTENTE
    // Permet à l'utilisateur de modifier les
    // quantités de sa commande avant qu'elle soit
    // prise en charge par la cuisine.
    //
    // Si le nouveau total > ancien total : redirection
    // vers la page de paiement du supplément.
    // Si nouveau total < ancien total : un ticket de
    // réduction est créé via edit_cmd.php.
    // ═══════════════════════════════════════════

    const modalCmd      = document.getElementById('edit-cmd-modal');
    const closeCmdBtn   = document.getElementById('close-cmd-modal');
    const btnsModifierCmd = document.querySelectorAll('.btn-modifier-cmd');
    const btnSaveCmd    = document.getElementById('btn-save-cmd');

    let currentCmdId  = null;  // ID de la commande en cours de modification
    let oldTotal      = 0;     // Montant original de la commande
    let currentArticles = [];  // Articles avec leurs quantités (modifiables)

    if (btnsModifierCmd.length > 0) {
        btnsModifierCmd.forEach(btn => {
            btn.addEventListener('click', () => {
                // Récupération des données de la commande depuis les attributs data-*
                currentCmdId     = btn.getAttribute('data-id');
                oldTotal         = parseFloat(btn.getAttribute('data-prix'));
                currentArticles  = JSON.parse(btn.getAttribute('data-articles'));

                document.getElementById('modal-cmd-id').textContent    = currentCmdId;
                document.getElementById('modal-old-price').textContent = oldTotal;

                renderModalItems(); // Affichage des articles dans la modale
                if (modalCmd) modalCmd.classList.add('active');
            });
        });
    }

    if (closeCmdBtn) {
        closeCmdBtn.addEventListener('click', () => modalCmd.classList.remove('active'));
    }

    /**
     * Génère le contenu de la modale de modification :
     * liste les articles avec boutons +/- pour changer les quantités.
     * Recalcule le total et appelle gererAffichageDifference().
     */
    function renderModalItems() {
        const container = document.getElementById('modal-cmd-items');
        if (!container) return;

        container.innerHTML = '';
        let newTotal = 0;

        currentArticles.forEach((art, index) => {
            if (art.quantite <= 0) return; // On n'affiche pas les articles supprimés

            newTotal += art.quantite * art.prix_unitaire;

            const div = document.createElement('div');
            div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; padding:8px 0;';
            div.innerHTML = `
                <div style="flex:1;">
                    <span style="color:#e8e2d9;">${art.nom}</span><br>
                    <span style="color:#888; font-size:0.8rem;">${art.prix_unitaire}€ / u</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <button type="button" class="btn-qty-minus" data-index="${index}"
                        style="background:#333; color:white; border:none; width:25px; height:25px; cursor:pointer;">-</button>
                    <span style="color:#bc9c64; font-weight:bold; width:20px; text-align:center;">${art.quantite}</span>
                    <button type="button" class="btn-qty-plus" data-index="${index}"
                        style="background:#333; color:white; border:none; width:25px; height:25px; cursor:pointer;">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        document.getElementById('modal-new-price').textContent = newTotal;
        gererAffichageDifference(newTotal); // Affiche un message si le total a changé

        // Branchement des boutons +/-
        document.querySelectorAll('.btn-qty-minus').forEach(b => b.addEventListener('click', (e) => updateQty(e, -1)));
        document.querySelectorAll('.btn-qty-plus').forEach(b => b.addEventListener('click',  (e) => updateQty(e, +1)));
    }

    /**
     * Modifie la quantité d'un article et remet à jour l'affichage.
     * La quantité minimum est 0 (suppression de l'article).
     */
    function updateQty(e, delta) {
        const index = e.target.getAttribute('data-index');
        currentArticles[index].quantite += delta;
        if (currentArticles[index].quantite < 0) currentArticles[index].quantite = 0;
        renderModalItems(); // Réaffichage complet avec le nouveau total
    }

    /**
     * Affiche un message informatif selon la différence entre
     * l'ancien et le nouveau total de la commande.
     * - Supplément → message orange (paiement supplémentaire requis)
     * - Économie    → message vert (ticket de réduction créé)
     * - Identique   → message masqué
     */
    function gererAffichageDifference(newTotal) {
        const msgDiv = document.getElementById('modal-diff-msg');
        if (!msgDiv) return;

        const diff = newTotal - oldTotal;

        if (diff > 0) {
            msgDiv.style.display   = 'block';
            msgDiv.style.background = 'rgba(245, 158, 11, 0.1)';
            msgDiv.style.color      = '#f59e0b';
            msgDiv.innerHTML = `⚠️ <b>Supplément de ${diff}€</b>. Vous devrez régler cette différence pour que la cuisine démarre.`;
        } else if (diff < 0) {
            msgDiv.style.display   = 'block';
            msgDiv.style.background = 'rgba(34, 197, 94, 0.1)';
            msgDiv.style.color      = '#22c55e';
            msgDiv.innerHTML = `🎁 <b>Économie de ${Math.abs(diff)}€</b>. Un ticket de réduction de ce montant sera ajouté à votre profil.`;
        } else {
            msgDiv.style.display = 'none';
        }
    }

    if (btnSaveCmd) {
        btnSaveCmd.addEventListener('click', async () => {
            // Feedback visuel pendant le traitement
            btnSaveCmd.textContent = "TRAITEMENT...";
            btnSaveCmd.style.opacity = '0.7';
            btnSaveCmd.style.pointerEvents = 'none';

            // On ne garde que les articles avec quantité > 0
            const articlesFinaux = currentArticles.filter(a => a.quantite > 0);

            try {
                // Envoi des modifications à edit_cmd.php (format JSON)
                const response = await fetch('../actions/edit_cmd.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_commande: currentCmdId,
                        articles:    articlesFinaux
                    })
                });

                const data = await response.json();

                if (data.success) {
                    btnSaveCmd.style.background = '#4caf50';
                    btnSaveCmd.style.color      = 'white';
                    btnSaveCmd.textContent      = 'COMMANDE MODIFIÉE ✓';

                    // Calcul du nouveau total pour décider de la redirection
                    let nouveauTotalFinal = 0;
                    articlesFinaux.forEach(a => nouveauTotalFinal += a.quantite * a.prix_unitaire);
                    const diff = nouveauTotalFinal - oldTotal;

                    setTimeout(() => {
                        if (diff > 0) {
                            // Supplément à payer → redirection vers la page de paiement
                            window.location.href = `paiement.php?cmd=${currentCmdId}&montant=${diff}`;
                        } else {
                            // Pas de supplément → simple rechargement
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

    /** Remet le bouton de sauvegarde dans son état initial après une erreur */
    function resetSaveBtn() {
        btnSaveCmd.textContent      = "VALIDER LES MODIFICATIONS";
        btnSaveCmd.style.opacity    = '1';
        btnSaveCmd.style.pointerEvents = 'auto';
        btnSaveCmd.style.background = '#bc9c64';
        btnSaveCmd.style.color      = 'black';
    }

    // ═══════════════════════════════════════════
    // 3. CONSULTATION D'UN AVIS EXISTANT
    // Quand l'utilisateur clique sur 👁️ à côté
    // d'une commande notée, on affiche les notes
    // et le commentaire dans une modale.
    // Les données de l'avis sont stockées en JSON
    // dans l'attribut data-avis du bouton.
    // ═══════════════════════════════════════════

    const modalAvis     = document.getElementById('modal-view-avis');
    const contentAvis   = document.getElementById('content-avis-popup');
    const btnCloseAvis  = document.getElementById('close-avis-modal');
    const btnCloseAvisPop = document.getElementById('btn-close-avis-popup');

    const btnsViewAvis = document.querySelectorAll('.btn-view-avis');
    if (btnsViewAvis.length > 0) {
        btnsViewAvis.forEach(btn => {
            btn.addEventListener('click', () => {
                try {
                    // Lecture des données d'avis depuis l'attribut data-avis
                    const data = JSON.parse(btn.getAttribute('data-avis'));

                    if (contentAvis) {
                        // Génération des étoiles : ★ pour les notes, ☆ pour le reste
                        contentAvis.innerHTML = `
                            <div style="margin-bottom:20px;">
                                <p style="color:#bc9c64; text-transform:uppercase; font-size:0.7rem; letter-spacing:1px;">Notes attribuées</p>
                                <p style="font-size:1.1rem; margin:10px 0;">Cuisine : <span style="color:#bc9c64;">${"★".repeat(data.produits)}${"☆".repeat(5 - data.produits)}</span></p>
                                <p style="font-size:1.1rem; margin:10px 0;">Service : <span style="color:#bc9c64;">${"★".repeat(data.livraison)}${"☆".repeat(5 - data.livraison)}</span></p>
                            </div>
                            <div style="border-top:1px solid #333; padding-top:20px;">
                                <p style="color:#bc9c64; text-transform:uppercase; font-size:0.7rem; letter-spacing:1px;">Commentaire</p>
                                <p style="font-style:italic; font-size:0.95rem; line-height:1.6; margin-top:10px; color:#ddd;">
                                    "${data.commentaire || 'Aucun commentaire laissé.'}"
                                </p>
                            </div>
                            <p style="font-size:0.65rem; color:#666; margin-top:25px;">Évalué le ${data.date_note || 'Date inconnue'}</p>
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

    /** Ferme la modale d'avis avec animation */
    const fermerAvis = () => {
        if (modalAvis) {
            modalAvis.classList.remove('active');
            setTimeout(() => { modalAvis.style.display = 'none'; }, 300);
        }
    };

    if (btnCloseAvis)    btnCloseAvis.addEventListener('click', fermerAvis);
    if (btnCloseAvisPop) btnCloseAvisPop.addEventListener('click', fermerAvis);

});