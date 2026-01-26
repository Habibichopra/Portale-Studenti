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
?>