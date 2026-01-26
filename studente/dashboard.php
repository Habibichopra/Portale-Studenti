<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classes/Compito.php';
require_once '../classes/Voto.php';
require_once '../classes/Corso.php';

$studente_id = $_SESSION['user_id'];

$compitoObj = new Compito();
$votoObj = new Voto();
$corsoObj = new Corso();
?>