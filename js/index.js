/* =========================================
   KAISEKI SHUNEI — INDEX.JS
   ========================================= */

let currentImg = 1;

function changeImage(direction) {
    const photo = document.getElementById("main-photo");
    currentImg += direction;
    if (currentImg > 6) currentImg = 1;
    if (currentImg < 1) currentImg = 6;
    photo.src = `img/resto-${currentImg}.png`;
}

function openGallery() {
    const s = document.getElementById("restaurant");
    s.scrollIntoView({ behavior: 'smooth' });
    setTimeout(() => {
        s.classList.add("gallery-active");
        document.body.classList.add("no-scroll");
        document.querySelector("header").style.opacity = "0";
    }, 600);
}

function closeGallery() {
    document.getElementById("restaurant").classList.remove("gallery-active");
    document.body.classList.remove("no-scroll");
    document.querySelector("header").style.opacity = "1";
}

const histoires = {
    kenji: {
        titre: "Maître Kenji",
        texte: "Né sous les neiges éternelles d'Hokkaido, Kenji a appris très tôt que la cuisine est une discipline de l'esprit avant d'être celle des mains. Son parcours l'a mené des ports de pêche glacés du Nord aux cuisines impériales de Tokyo. Maître incontesté du feu et de la découpe, il traite chaque ingrédient avec la dévotion d'un forgeron de katana."
    },
    aiko: {
        titre: "Chef Aiko",
        texte: "Originaire de Kyoto, le cœur culturel du Japon, Aiko a grandi au rythme des jardins de thé et des temples séculaires. Elle conçoit ses assiettes comme des haïkus comestibles, où le vide est aussi important que la matière. Formée à l'art de la calligraphie et de l'ikebana, elle apporte à Shunei une sensibilité rare."
    }
};

function ouvrirHistoire(chef) {
    document.getElementById('story-content').innerHTML = `
        <h2>${histoires[chef].titre}</h2>
        <p>${histoires[chef].texte}</p>
    `;
    document.getElementById('chef-overlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function fermerHistoire() {
    document.getElementById('chef-overlay').style.display = 'none';
    document.body.style.overflow = 'auto';
}


/* =========================================
   NOUVEAU : VALIDATION FORMULAIRE DE CONNEXION
   ========================================= */
document.addEventListener('DOMContentLoaded', () => {
    const formConnexion = document.querySelector('#form-connexion');
    if (!formConnexion) return;

    const emailInput = formConnexion.querySelector('input[name="email"]');
    const passwordInput = formConnexion.querySelector('input[name="password"]');
    const btnSubmit = formConnexion.querySelector('.btn-submit');
    
    // 1. AFFICHER / MASQUER LE MOT DE PASSE
    const togglePassword = document.querySelector('#toggleLoginPassword');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }

    // 2. COMPTEURS DE CARACTÈRES
    const counterEmail = document.querySelector('#counter-login-email');
    const counterMdp = document.querySelector('#counter-login-mdp');

    function updateCounter(inputElement, counterElement) {
        if (!inputElement || !counterElement) return;
        const currentLength = inputElement.value.length;
        const maxLength = inputElement.getAttribute('maxlength');
        counterElement.textContent = `${currentLength} / ${maxLength}`;
        
        if (currentLength >= maxLength) {
            counterElement.style.color = '#ff4444'; // Rouge si limite atteinte
        } else {
            counterElement.style.color = '#888';
        }
    }

    if (emailInput) {
        emailInput.addEventListener('input', () => updateCounter(emailInput, counterEmail));
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', () => updateCounter(passwordInput, counterMdp));
    }

    // 3. VALIDATION AVANT ENVOI AU SERVEUR
    formConnexion.addEventListener('submit', (e) => {
        let isValid = true;
        
        // Reset des styles d'erreur (si précédemment modifiés)
        emailInput.style.outline = 'none';
        passwordInput.style.outline = 'none';

        // Vérification de l'email (Regex)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            emailInput.style.outline = '1px solid #ff4444';
        }

        // Vérification du mot de passe (ne doit pas être vide)
        if (passwordInput.value.trim() === '') {
            isValid = false;
            passwordInput.style.outline = '1px solid #ff4444';
        }

        // Si erreur, on bloque le rechargement de la page
        if (!isValid) {
            e.preventDefault();
            
            // Animation de secousse sur le bouton
            btnSubmit.classList.add('shake');
            setTimeout(() => btnSubmit.classList.remove('shake'), 500);
        } else {
            // Effet visuel pendant l'envoi
            btnSubmit.textContent = 'CONNEXION...';
            btnSubmit.style.opacity = '0.7';
        }
    });
    
    // Ajout dynamique de l'animation CSS "shake" si elle n'existe pas
    if (!document.querySelector('#shake-style')) {
        const style = document.createElement('style');
        style.id = 'shake-style';
        style.textContent = `
            .shake { animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97); }
            @keyframes shake {
                10%, 90% { transform: translateX(-2px); }
                20%, 80% { transform: translateX(4px); }
                30%, 50%, 70% { transform: translateX(-6px); }
                40%, 60% { transform: translateX(6px); }
            }
        `;
        document.head.appendChild(style);
    }
});