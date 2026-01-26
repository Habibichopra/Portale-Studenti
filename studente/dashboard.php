<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classes/Compito.php';
require_once '../classes/Voto.php';
require_once '../classes/Corso.php';

$studente_id = $_SESSION['user_id'];

$compitoObj = new Compito();
$votoObj = new Voto();
$corsoObj = new Corso();

$compiti_scadenza = $compitoObj->getCompitiProssimi(7, $studente_id);

$tutti_voti = $votoObj->getVotiByStudente($studente_id);
$ultimi_voti = array_slice($tutti_voti, 0, 5);

$media_voti = $votoObj->calcolaMedia($studente_id);

$corsi_attivi = $corsoObj->getCorsiByStudente($studente_id);
$num_corsi = count($corsi_attivi);

define('PAGE_TITLE', 'Dashboard Studente');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container">

    <header class="dashboard-header">
        <h1>Ciao, <?php echo htmlspecialchars($_SESSION['nome_completo']); ?>! 👋</h1>
        <p class="sottotitolo">Ecco la situazione aggiornata della tua carriera scolastica.</p>
    </header>
    
</div>

<?php include '../inclusi/footer.php'; ?>  