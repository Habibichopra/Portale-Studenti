<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classes/Corso.php';

$corsoObj = new Corso();
$studente_id = $_SESSION['user_id'];

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['azione']) && $_POST['azione'] === 'disiscriviti') {
        $iscrizione_id = $_POST['iscrizione_id'];
        if ($corsoObj->rimuoviIscrizione($iscrizione_id)) {
            $messaggio = "Ti sei disiscritto dal corso con successo.";
        } else {
            $errore = "errore durante la disiscrizione.";
        }
    }

    if (isset($_POST['azione']) && $_POST['azione'] === 'iscriviti') {
        $corso_id = $_POST['corso_id'];
        if ($corsoObj->iscriviStudente($studente_id, $corso_id)) {
            $messaggio = "Iscrizione effettuata! Ora puoi vedere i materiali.";
        } else {
            $errore = "Impossibile iscriversi. Forse sei già iscritto?";
        }
    }
}
?>