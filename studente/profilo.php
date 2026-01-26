<?php

require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/User.php';
$userObj = new User();
$user_id = $_SESSION['user_id'];

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $conf_password = $_POST['conf_password'];

        
    if (empty($nome) || empty($cognome) || empty($email)) {
        $errore = "Nome, Cognome ed Email sono obbligatori.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "Formato email non valido.";
    } elseif (!empty($password) && $password !== $conf_password) {
        $errore = "Le nuove password non coincidono.";
    } elseif (!empty($password) && strlen($password) < 8) {
         $errore = "La password deve essere di almeno 8 caratteri.";
    } else {
        $dati = [
            'nome' => $nome,
            'cognome' => $cognome,
            'email' => $email,
            'password' => !empty($password) ? $password : null // Se vuota, non cambia
        ];

        if ($userObj->updateProfile($user_id, $dati)) {
            $messaggio = "Profilo aggiornato con successo!";
            $_SESSION['nome_completo'] = $nome . " " . $cognome;
        } else {
            $errore = "errore durante l'aggiornamento. L'email potrebbe essere già in uso.";
        }
    }
}

$utente = $userObj->getUserById($user_id);

define('PAGE_TITLE', 'Il mio Profilo');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">

    <header class="header-pagina">
        <h1><i class="fas fa-user-cog"></i> Profilo Utente</h1>
        <p>Gestisci le tue informazioni personali e di accesso.</p>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><?php echo $messaggio; ?></div>
    <?php endif; ?>
    
    <?php if ($errore): ?>
        <div class="alert alert-errore"><?php echo $errore; ?></div>
    <?php endif; ?>

    <div class="griglia-profilo">
        <div class="scheda testo-centrato">
            <div class="body-scheda">
                
                
            </div>
        </div>
    </div>

</div>


<?php include '../inclusi/footer.php'; ?>

