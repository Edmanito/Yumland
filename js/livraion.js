function validerLivraison(id) {
    const card = document.getElementById(id);
    
    if(confirm("Confirmer la remise en main propre ?")) {
        card.style.transform = "translateX(100px)";
        card.style.opacity = "0";
        
        setTimeout(() => {
            card.style.display = "none";
            alert("Livraison Shunei validée. Félicitations pour votre course.");
        }, 400);
    }
}