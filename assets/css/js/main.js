

document.addEventListener('DOMContentLoaded', function() {
    

    // 1. GESTIONE MENU MOBILE (Hamburger)
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('open');
        });
    }

    // 2. GESTIONE ALERT (Scomparsa automatica)
    const alerts = document.querySelectorAll('.alert');

    if (alerts.length > 0) {
        // Dopo 5 secondi (5000 ms), inizia a nasconderli
        setTimeout(() => {
            alerts.forEach(alert => {
                // Aggiunge transizione CSS per l'effetto dissolvenza
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";

                // Dopo che l'animazione è finita, rimuove l'elemento dal DOM
                setTimeout(() => {
                    alert.style.display = "none";
                }, 500); // 500ms corrisponde alla durata della transition
            });
        }, 5000);
    }

    // 3. CONFERMA ELIMINAZIONE GLOBALE
    // Esempio di gestione link "Elimina" se non sono dentro un form:
    const deleteLinks = document.querySelectorAll('.btn-delete-confirm');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Sei sicuro di voler procedere con l\'eliminazione? Questa azione è irreversibile.')) {
                e.preventDefault();
            }
        });
    });

 
    // 4. GESTIONE ACCORDION (Messaggi/Comunicazioni)
    const messaggioHeaders = document.querySelectorAll('.msg-header');
    
    messaggioHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const icon = this.querySelector('.fa-chevron-down');
            if (icon) {

            }
        });
    });

});


function stampaPagina() {
    window.print();
}