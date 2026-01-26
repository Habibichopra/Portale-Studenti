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

foreach ($corsi as $corso) {
    $compiti_corso = $compitoObj->getCompitiByCorso($corso['id']);

    foreach ($compiti_corso as $compito) {

        $compito['nome_corso'] = $corso['nome_corso'];
        $compito['codice_corso'] = $corso['codice_corso'];

        $consegna_effettuata = isset($mappa_consegne[$compito['id']]) ? $mappa_consegne[$compito['id']] : null;
        
        $data_scadenza = new DateTime($compito['data_scadenza']);
        $oggi = new DateTime();

        if ($consegna_effettuata) {
            $compito['stato_utente'] = $consegna_effettuata['stato'];
            $compito['dati_consegna'] = $consegna_effettuata;
            $lista_storico[] = $compito;
        } elseif ($oggi > $data_scadenza) {
            $compito['stato_utente'] = 'mancante';
            $lista_storico[] = $compito;
        } else {
            $compito['stato_utente'] = 'da_fare';
            $lista_da_fare[] = $compito;
        }
    }
}

usort($lista_da_fare, function($a, $b) {
    return strtotime($a['data_scadenza']) - strtotime($b['data_scadenza']);
});

usort($lista_storico, function($a, $b) {
    return strtotime($b['data_scadenza']) - strtotime($a['data_scadenza']);
});

define('PAGE_TITLE', 'I Miei Compiti');
?>