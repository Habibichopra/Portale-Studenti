<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../includes/session_check.php';

require_once '../classes/Corso.php';
require_once '../classes/Compito.php';
require_once '../classes/Consegna.php';

$studente_id = $_SESSION['user_id'];

$corsoObj = new Corso();
$compitoObj = new Compito();
$consegnaObj = new Consegna();

$corsi = $corsoObj->getCorsiByStudente($studente_id);
$tutte_consegne = $consegnaObj->getConsegneByStudente($studente_id);

$mappa_consegne = [];
foreach ($tutte_consegne as $c) {
    $mappa_consegne[$c['compito_id']] = $c;
}

$lista_da_fare = [];
$lista_storico = [];
?>