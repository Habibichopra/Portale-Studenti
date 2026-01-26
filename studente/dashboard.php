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

    <div class="griglia-statistiche">
        <div class="scheda-statistiche <?php echo ($media_voti >= 24) ? 'verde' : 'rosso'; ?>">
            <div class="icona-statistiche"><i class="fas fa-chart-line"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $media_voti; ?></h3>
                <p>Media Generale</p>
            </div>
        </div>

        <div class="scheda-statistiche">
            <div class="icona-statistiche"><i class="fas fa-book"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $num_corsi; ?></h3>
                <p>Corsi Attivi</p>
            </div>
        </div>

        <div class="scheda-statistiche <?php echo (count($compiti_scadenza) > 0) ? 'urgente' : 'blu'; ?>">
            <div class="icona-statistiche"><i class="fas fa-tasks"></i></div>
            <div class="info-statistiche">
                <h3><?php echo count($compiti_scadenza); ?></h3>
                <p>Compiti in scadenza (7gg)</p>
            </div>
        </div>
    </div>

    <div class="dashboard-main-griglia">
        <section class="scheda">
             <div class="scheda-header">
                <h2><i class="fas fa-clock"></i> In Scadenza</h2>
                <a href="compiti.php" class="btn-testo">Vedi tutti &rarr;</a>
            </div>

            <div class="body-scheda">
                
            </div>

        </section>
    </div>

    

</div>

<?php include '../inclusi/footer.php'; ?>  