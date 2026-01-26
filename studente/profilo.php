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
    
}
?>

