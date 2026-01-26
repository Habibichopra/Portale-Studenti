<?php

require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Comunicazione.php';
require_once '../classi/User.php';
require_once '../classi/Corso.php';

$studente_id = $_SESSION['user_id'];
$comunicazioneObj = new Comunicazione();
$userObj = new User();
$corsoObj = new Corso();

?>