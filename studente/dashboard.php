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
                    <ul class="lista-task">
                        <?php foreach ($compiti_scadenza as $task): ?>
                            <?php 

                                $scadenza = new DateTime($task['data_scadenza']);
                                $oggi = new DateTime();
                                $diff = $oggi->diff($scadenza);
                                $giorni_mancanti = $diff->days;
                                $is_urgente = ($giorni_mancanti <= 2);
                            ?>
                            <li class="elemento-task <?php echo $is_urgente ? 'contorno-urgente' : ''; ?>">
                                <div class="task-info">
                                    <span class="avviso-corso"><?php echo htmlspecialchars($task['codice_corso']); ?></span>
                                    <h4><?php echo htmlspecialchars($task['titolo']); ?></h4>
                                    <span class="data-task">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo date('d/m/Y H:i', strtotime($task['data_scadenza'])); ?>
                                        <?php if($is_urgente): ?>
                                            <span class="avviso-urgente">Scade presto!</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="azione-task">
                                    <a href="consegna.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-primario">Consegna</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="nessun-contenuto">
                        <i class="fas fa-mug-hot"></i>
                        <p>Nessun compito in scadenza. Rilassati!</p>
                    </div>
                <?php endif; ?>

            </div>

        </section>
    </div>

    

</div>

<?php include '../inclusi/footer.php'; ?>  