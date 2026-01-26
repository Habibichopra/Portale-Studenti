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

foreach ($consegne_studente as $c) {
    if ($c['compito_id'] == $compito_id) {
        $consegna_esistente = $c;
        break;
    }
}

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_consegna'])) {
    
    $data_scadenza = new DateTime($task['data_scadenza']);
    $adesso = new DateTime();
    
    if ($adesso > $data_scadenza) {
        $errore = "Tempo scaduto! Non puoi più consegnare questo compito.";
    } elseif ($consegna_esistente) {
        $errore = "Hai già effettuato una consegna per questo compito.";
    } else {
        $note = $_POST['note'] ?? '';
        $file = $_FILES['file_consegna'];

        if ($consegnaObj->consegnaCompito($compito_id, $studente_id, $file, $note)) {
            header("Location: consegna.php?id=" . $compito_id . "&success=1");
            exit;
        } else {
            $errore = "errore durante il caricamento. Controlla il formato del file.";
        }
    }
}

if (isset($_GET['success'])) {
    $messaggio = "Compito consegnato con successo!";
}

?>