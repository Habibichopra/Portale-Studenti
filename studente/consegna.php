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

?>