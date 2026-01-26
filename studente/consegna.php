<?php

require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classu/Compito.php';
require_once '../classi/Consegna.php';

if (!isset($_GET['id'])) {
    header("Location: compiti.php"); 
    exit;
}

$compito_id = $_GET['id'];
$studente_id = $_SESSION['user_id'];
$compitoObj = new Compito();
$consegnaObj = new Consegna();

$task = $compitoObj->getCompitoById($compito_id);
if (!$task) {
    die("Compito non trovato.");
}

$consegne_studente = $consegnaObj->getConsegneByStudente($studente_id);
$consegna_esistente = null;


?>