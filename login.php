<?php
require_once 'config/config.php';
require_once 'classi/User.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errore = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $user = new User();
        
        if ($user->login($username, $password)) {

            switch ($_SESSION['ruolo']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'professore':
                    header("Location: professore/dashboard.php");
                    break;
                case 'studente':
                    header("Location: studente/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit;
        } else {
            $errore = "Username o Password non validi.";
        }
    } else {
        $errore = "Per favore compila tutti i campi.";
    }
}

include 'inclusi/header.php';
include 'inclusi/nav.php';
?>


<div class="container layout-contenuto">
    <div class="form-container">
        <h2>Accedi al Portale</h2>
        
        <?php if($errore): ?>
            <div class="alert alert-errore"><?php echo $errore; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="gruppo-form">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required class="controllo-form">
            </div>

            <div class="gruppo-form">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required class="controllo-form">
            </div>

            <button type="submit" class="btn btn-primario btn-blocco">Accedi</button>
        </form>

        <p class="testo-centrato mt-3">
            Non hai un account? <a href="register.php">Registrati qui</a>
        </p>
    </div>
</div>

<?php include 'inclusi/footer.php'; ?>