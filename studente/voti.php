<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Voto.php';
require_once '../classi/EsportatoreCSV.php';

$studente_id = $_SESSION['user_id'];
$votoObj = new Voto();
$csvExporter = new EsportatoreCSV();


$download_link = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    
}

?>