<?php
require_once 'config/config.php';
require_once 'classi/User.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$messaggio = '';
$errore = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ruolo = $_POST['ruolo'];
    $matricola = $_POST['matricola'] ?? null; 

    if ($ruolo == 'studente' && empty($matricola)) {
        $errore = "La matricola è obbligatoria per gli studenti.";
    } else {
        $user = new User();
        if ($user->registra($username, $password, $email, $nome, $cognome, $ruolo, $matricola)) {
            $messaggio = "Registrazione avvenuta con successo! <a href='login.php'>Accedi ora</a>.";
        } else {
            $errore = "Errore durante la registrazione. Username o Email potrebbero essere già in uso.";
        }
    }
}


include 'inclusi/header.php';
include 'inclusi/nav.php';
?>



<div class="container layout-contenuto">
    <div class="form-container">
        <h2>Crea un Account</h2>

        <?php if($messaggio): ?>
            <div class="alert alert-successo"><?php echo $messaggio; ?></div>
        <?php endif; ?>
        
        <?php if($errore): ?>
            <div class="alert alert-erroro"><?php echo $errore; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="riga">
                <div class="colonna-meta">
                    <div class="gruppo-form">
                        <label>Nome</label>
                        <input type="text" name="nome" required class="controllo-form">
                    </div>
                </div>
                <div class="colonna-meta">
                    <div class="gruppo-form">
                        <label>Cognome</label>
                        <input type="text" name="cognome" required class="controllo-form">
                    </div>
                </div>
            </div>

            <div class="gruppo-form">
                <label>Email</label>
                <input type="email" name="email" required class="controllo-form">
            </div>

            <div class="gruppo-form">
                <label>Username</label>
                <input type="text" name="username" required class="controllo-form">
            </div>

            <div class="gruppo-form">
                <label>Password</label>
                <input type="password" name="password" required class="controllo-form" minlength="4">
                <small>Minimo 4 caratteri</small>
            </div>

            <div class="gruppo-form">
                <label>Ruolo</label>
                <select name="role" id="role-select" class="controllo-form" onchange="toggleMatricola()">
                    <option value="studente">Studente</option>
                    <option value="professore">Professore</option>
                </select>
            </div>

            <div class="gruppo-form" id="matricola-group">
                <label>Matricola</label>
                <input type="text" name="matricola" class="controllo-form" placeholder="Es: MAT123">
            </div>

            <button type="submit" class="btn btn-primario btn-blocco">Registrati</button>
        </form>

        <p class="testo-centrato mt-3">
            Hai già un account? <a href="login.php">Accedi</a>
        </p>
    </div>
</div>

<script>
function toggleMatricola() {
    var role = document.getElementById('role-select').value;
    var matricolaGroup = document.getElementById('matricola-group');
    if (role === 'professore') {
        matricolaGroup.style.display = 'none';
    } else {
        matricolaGroup.style.display = 'block';
    }
}
</script>

<?php include 'inclusi/footer.php'; ?>