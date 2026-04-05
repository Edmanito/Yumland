/* =========================================
   KAISEKI SHUNEI — INDEX.JS
   ========================================= */

let currentImg = 1;

/* ── GALERIE RESTAURANT ── */
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

/* ── HISTOIRES CHEFS ── */
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