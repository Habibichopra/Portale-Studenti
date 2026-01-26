<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Materiale.php';

$studente_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$materialeObj = new Materiale();

$miei_corsi = $corsoObj->getCorsiByStudente($studente_id);

$corsi_map = [];
foreach ($miei_corsi as $c) {
    $corsi_map[$c['id']] = $c;
}

$corso_selezionato = isset($_GET['corso_id']) ? $_GET['corso_id'] : 'tutti';

$lista_materiali = [];

if ($corso_selezionato !== 'tutti') {
    
    if (!array_key_exists($corso_selezionato, $corsi_map)) {
        header("Location: materiali.php");
        exit;
    }
    
    $materiali_base = $materialeObj->getMaterialiByCorso($corso_selezionato);
    
    foreach ($materiali_base as $m) {
        $m['nome_corso'] = $corsi_map[$m['corso_id']]['nome_corso'];
        $m['codice_corso'] = $corsi_map[$m['corso_id']]['codice_corso'];
        $lista_materiali[] = $m;
    }

} else {

    foreach ($miei_corsi as $corso) {
        $mats = $materialeObj->getMaterialiByCorso($corso['id']);
        foreach ($mats as $m) {
            $m['nome_corso'] = $corso['nome_corso'];
            $m['codice_corso'] = $corso['codice_corso'];
            $lista_materiali[] = $m;
        }
    }
    
    usort($lista_materiali, function($a, $b) {
        return strtotime($b['data_upload']) - strtotime($a['data_upload']);
    });
}
?>