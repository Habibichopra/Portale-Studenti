
document.addEventListener('DOMContentLoaded', function() {


    // 1. VALIDAZIONE REGISTRAZIONE & PROFILO
    const formsToValidate = document.querySelectorAll('form[azione="register.php"], form[azione="profilo.php"]');

    formsToValidate.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            let errores = [];

            const passwordInput = form.querySelector('input[name="password"]');
            const confirmPassInput = form.querySelector('input[name="conf_password"]'); // Se esiste (profilo)

            if (passwordInput && passwordInput.value !== "") {
                const pwd = passwordInput.value;
                
                // Regole: Min 8 char, 1 Maiuscola, 1 Numero, 1 Speciale
                // Regex spiegata:
                // (?=.*[A-Z]) -> Almeno una maiuscola
                // (?=.*[0-9]) -> Almeno un numero
                // (?=.*[!@#\$%\^&\*]) -> Almeno un carattere speciale
                // .{8,}       -> Lunghezza minima 8
                const strongPasswordRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/;

                if (!strongPasswordRegex.test(pwd)) {
                    isValid = false;
                    errores.push("La password deve avere almeno 8 caratteri, una maiuscola, un numero e un carattere speciale (!@#$%^&*).");
                    highlighterrore(passwordInput);
                } else {
                    removeerrore(passwordInput);
                }

                // Controllo corrispondenza (solo per profilo.php)
                if (confirmPassInput && pwd !== confirmPassInput.value) {
                    isValid = false;
                    errores.push("Le due password non coincidono.");
                    highlighterrore(confirmPassInput);
                }
            }

            // --- B. Validazione Username (solo register) ---
            const usernameInput = form.querySelector('input[name="username"]');
            if (usernameInput) {
                // Solo lettere e numeri, 4-20 caratteri
                const userRegex = /^[a-zA-Z0-9]{4,20}$/;
                if (!userRegex.test(usernameInput.value)) {
                    isValid = false;
                    errores.push("L'username deve essere alfanumerico e tra 4 e 20 caratteri.");
                    highlighterrore(usernameInput);
                } else {
                    removeerrore(usernameInput);
                }
            }

            // --- C. Validazione Email ---
            const emailInput = form.querySelector('input[name="email"]');
            if (emailInput) {
                // Regex standard per email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    isValid = false;
                    errores.push("Inserisci un indirizzo email valido.");
                    highlighterrore(emailInput);
                } else {
                    removeerrore(emailInput);
                }
            }

            if (!isValid) {
                event.preventDefault(); // Blocca il form
                alert("Attenzione:\n- " + errores.join("\n- "));
            }
        });
    });

    // 2. CONTROLLO DIMENSIONE FILE (UPLOAD)
    const fileInputs = document.querySelectorAll('input[type="file"]');

    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fileSize = this.files[0].size; // Dimensione in bytes
                const maxSize = 10 * 1024 * 1024;    // 10 MB in bytes

                if (fileSize > maxSize) {
                    alert("Il file selezionato è troppo grande! Il limite massimo è 10MB.");
                    this.value = ""; // Resetta il campo file
                    

                    this.classList.add('input-errore');
                } else {
                    this.classList.remove('input-errore');
                }
            }
        });
    });

    // 3. FUNZIONI DI UTILITÀ GRAFICA
    function highlighterrore(element) {
        element.style.borderColor = "#e74c3c"; // Rosso
        element.style.backgroundColor = "#fdedec"; // Sfondo rossiccio
    }

    function removeerrore(element) {
        element.style.borderColor = "#ddd"; // Torna normale
        element.style.backgroundColor = "#fff";
    }

});