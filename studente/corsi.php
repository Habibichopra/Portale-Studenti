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

$miei_corsi = $corsoObj->getCorsiByStudente($studente_id);
$tutti_corsi = $corsoObj->getAllCorsi();

$ids_miei_corsi = array_column($miei_corsi, 'id');
$corsi_disponibili = [];

foreach ($tutti_corsi as $corso) {
    if (!in_array($corso['id'], $ids_miei_corsi)) {
        $corsi_disponibili[] = $corso;
    }
}

define('PAGE_TITLE', 'I Miei Corsi');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">

</div>


<?php include '../inclusi/footer.php'; ?>  