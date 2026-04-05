/* =========================================
   KAISEKI SHUNEI — INSCRIPTION.JS
   Validations, animations, UX premium
   ========================================= */

document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('form');
    if (!form) return;

    // ── RÉFÉRENCES ──
    const inputs = form.querySelectorAll('input, select');
    const btnSubmit = form.querySelector('.btn-submit');
    const mdpInput = form.querySelector('input[name="mdp"]');

    // ── 1. INDICATEUR DE FORCE DU MOT DE PASSE ──
    if (mdpInput) {
        const strengthBar  = document.querySelector('.password-strength-bar');
        const strengthText = document.querySelector('.strength-text');

        mdpInput.addEventListener('input', () => {
            const val = mdpInput.value;
            const score = getPasswordScore(val);

            const levels = [
                { pct: '0%',   color: 'transparent', label: '' },
                { pct: '25%',  color: '#ff4444',      label: 'FAIBLE' },
                { pct: '50%',  color: '#ff9944',      label: 'MOYEN' },
                { pct: '75%',  color: '#ffcc44',      label: 'BON' },
                { pct: '100%', color: '#44cc88',      label: 'EXCELLENT' },
            ];

            const lvl = levels[score];
            if (strengthBar)  { strengthBar.style.width = lvl.pct; strengthBar.style.background = lvl.color; }
            if (strengthText) { strengthText.textContent = lvl.label; strengthText.style.color = lvl.color; }
        });
    }

    function getPasswordScore(val) {
        if (!val) return 0;
        let score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        return Math.min(4, Math.ceil(score / 1.25));
    }

    // ── 2. VALIDATION EN TEMPS RÉEL ──
    const rules = {
        prenom:        { min: 2,   msg: 'Au moins 2 caractères.' },
        nom:           { min: 2,   msg: 'Au moins 2 caractères.' },
        login:         { email: true, msg: 'Email invalide.' },
        telephone:     { phone: true, msg: 'Numéro invalide (ex: 06 12 34 56 78).' },
        adresse_rue:   { min: 5,   msg: 'Adresse trop courte.' },
        adresse_ville: { min: 2,   msg: 'Ville requise.' },
        adresse_cp:    { cp: true, msg: 'Code postal invalide (5 chiffres).' },
        mdp:           { min: 8,   msg: 'Minimum 8 caractères.' },
    };

    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            if (input.classList.contains('error')) validateField(input);
        });
    });

    function validateField(input) {
        const name = input.name;
        const rule = rules[name];
        if (!rule) return true;

        const val = input.value.trim();
        let valid = true;

        if (rule.min && val.length < rule.min) valid = false;
        if (rule.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) valid = false;
        if (rule.phone && !/^(\+33|0)[1-9](\s?\d{2}){4}$/.test(val.replace(/\s/g, '').replace(/\./g, ''))) valid = false;
        if (rule.cp && !/^\d{5}$/.test(val)) valid = false;

        showFieldError(input, valid ? null : rule.msg);
        return valid;
    }

    function showFieldError(input, msg) {
        input.classList.toggle('error', !!msg);
        let errEl = input.parentElement.querySelector('.field-error');
        if (!errEl) {
            errEl = document.createElement('span');
            errEl.className = 'field-error';
            input.parentElement.appendChild(errEl);
        }
        errEl.textContent = msg || '';
        errEl.classList.toggle('visible', !!msg);
    }

    // ── 3. VALIDATION AU SUBMIT ──
    form.addEventListener('submit', (e) => {
        let allValid = true;

        inputs.forEach(input => {
            if (!validateField(input)) allValid = false;
        });

        if (!allValid) {
            e.preventDefault();
            // Scroll vers la première erreur
            const firstError = form.querySelector('input.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            // Shake le bouton
            btnSubmit.classList.add('shake');
            setTimeout(() => btnSubmit.classList.remove('shake'), 500);
            return;
        }

        // Loading state
        btnSubmit.classList.add('loading');
        btnSubmit.querySelector('span').textContent = 'CRÉATION EN COURS';
    });

    // ── 4. FORMAT AUTOMATIQUE TÉLÉPHONE ──
    const telInput = form.querySelector('input[name="telephone"]');
    if (telInput) {
        telInput.addEventListener('input', () => {
            let val = telInput.value.replace(/\D/g, '').substring(0, 10);
            if (val.length > 0) {
                val = val.match(/.{1,2}/g).join(' ');
            }
            telInput.value = val;
        });
    }

    // ── 5. FORMAT CODE POSTAL ──
    const cpInput = form.querySelector('input[name="adresse_cp"]');
    if (cpInput) {
        cpInput.addEventListener('input', () => {
            cpInput.value = cpInput.value.replace(/\D/g, '').substring(0, 5);
        });

        // Auto-détection arrondissement depuis le code postal
        cpInput.addEventListener('blur', () => {
            const cp = cpInput.value;
            const arrSelect = form.querySelector('select[name="arrondissement"]');
            if (arrSelect && /^750\d{2}$/.test(cp)) {
                const arr = parseInt(cp.substring(3));
                if (arr >= 1 && arr <= 20) {
                    arrSelect.value = arr;
                }
            }
        });
    }

    // ── 6. ANIMATION FOCUS LABELS ──
    inputs.forEach(input => {
        // Highlight de la section parente
        input.addEventListener('focus', () => {
            const section = input.closest('.form-section');
            if (section) section.style.opacity = '1';
        });
    });

    // ── 7. ANIMATION D'ENTRÉE STAGGERÉE DES SECTIONS ──
    const sections = document.querySelectorAll('.form-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    sections.forEach(section => {
        observer.observe(section);
    });

    // ── 8. SHAKE ANIMATION CSS (ajout dynamique) ──
    const style = document.createElement('style');
    style.textContent = `
        .btn-submit.shake {
            animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }
        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-6px); }
            40%, 60% { transform: translateX(6px); }
        }
    `;
    document.head.appendChild(style);

    // ── 9. CONFIRMATION VISUELLE AU FOCUS ──
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transition = 'all 0.3s ease';
        });
    });

    // ── 10. PRÉVISUALISATION NOM COMPLET ──
    const prenomInput = form.querySelector('input[name="prenom"]');
    const nomInput    = form.querySelector('input[name="nom"]');
    const brandOverlay = document.querySelector('.image-brand-overlay h2');

    function updateBrand() {
        if (!brandOverlay) return;
        const prenom = prenomInput?.value || '';
        const nom    = nomInput?.value    || '';
        if (prenom || nom) {
            brandOverlay.innerHTML = `Bienvenue, <em>${prenom} ${nom}</em>`;
        } else {
            brandOverlay.innerHTML = `Kaiseki <em>Shunei</em>`;
        }
    }

    if (prenomInput) prenomInput.addEventListener('input', updateBrand);
    if (nomInput)    nomInput.addEventListener('input',    updateBrand);

});