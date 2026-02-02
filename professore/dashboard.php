<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Compito.php';
require_once '../classi/Consegna.php';
require_once '../classi/Comunicazione.php';


$prof_id = $_SESSION['user_id'];

$corsoObj = new Corso();
$compitoObj = new Compito();
$consegnaObj = new Consegna();
$comunicazioneObj = new Comunicazione();


$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);

$totale_da_correggere = 0;
$totale_studenti_iscritti = 0; 

$messaggi_non_letti = 0;
$tutti_messaggi = $comunicazioneObj->getComunicazioniByUser($prof_id);
foreach($tutti_messaggi as $msg) {
    if($msg['letto'] == 0) {
        $messaggi_non_letti++;
    }
}


foreach ($miei_corsi as &$corso) {
    $compiti_corso = $compitoObj->getCompitiByCorso($corso['id']);
    $da_correggere_corso = 0;

    foreach ($compiti_corso as $compito) {
        $consegne = $consegnaObj->getConsegneByCompito($compito['id']);
        foreach ($consegne as $cons) {
            if ($cons['voto'] === null && ($cons['stato'] == 'consegnato' || $cons['stato'] == 'in_ritardo')) {
                $da_correggere_corso++;
                $totale_da_correggere++;
            }
        }
    }
    $corso['daCorreggere'] = $da_correggere_corso;
}
unset($corso); 

define('PAGE_TITLE', 'Dashboard Docente');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container dashboard-container">
    
    <header class="dashboard-header flex-header">
        <div>
            <h1>Bentornato, Prof. <?php echo htmlspecialchars($_SESSION['nome_completo']); ?> 👨‍🏫</h1>
            <p class="sottotitolo">Gestisci i tuoi corsi e valuta gli studenti.</p>
        </div>
        <div>
            <a href="corsi.php?action=create" class="btn btn-primario">
                <i class="fas fa-plus"></i> Nuovo Corso
            </a>
        </div>
    </header>

    <div class="griglia-statistiche">
        <div class="scheda-statistiche">
            <div class="icona-statistiche"><i class="fas fa-chalkboard"></i></div>
            <div class="info-statistiche">
                <h3><?php echo count($miei_corsi); ?></h3>
                <p>Corsi Attivi</p>
            </div>
        </div>

        <div class="scheda-statistiche <?php echo ($totale_da_correggere > 0) ? 'urgente' : 'verde'; ?>">
            <div class="icona-statistiche"><i class="fas fa-marker"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $totale_da_correggere; ?></h3>
                <p>Compiti da Valutare</p>
            </div>
        </div>

        <div class="scheda-statistiche viola">
            <div class="icona-statistiche"><i class="fas fa-envelope"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $messaggi_non_letti; ?></h3>
                <p>Nuovi Messaggi</p>
            </div>
            <a href="comunicazioni.php" class="card-link">Vedi</a>
        </div>
    </div>

    <hr class="separatore">

    <section>
        <h2><i class="fas fa-book"></i> I Tuoi Corsi</h2>
        
        <?php if (count($miei_corsi) > 0): ?>
            <div class="griglia-corsi">
                <?php foreach ($miei_corsi as $corso): ?>
                    <div class="scheda-corso">
                        <div class="header-corso-prof">
                            <span class="etichetta-codice"><?php echo htmlspecialchars($corso['codice_corso']); ?></span>
                            <div class="dropdown">
                                <a href="corsi.php?edit=<?php echo $corso['id']; ?>" class="icon-btn"><i class="fas fa-cog"></i></a>
                            </div>
                        </div>
                        
                        <div class="body-corso">
                            <h3><?php echo htmlspecialchars($corso['nome_corso']); ?></h3>
                            <p class="testo-disattivato">Anno: <?php echo htmlspecialchars($corso['anno_accademico']); ?></p>
                            
                            <?php if ($corso['daCorreggere'] > 0): ?>
                                <div class="alert-mini stato-pericolo">
                                    <i class="fas fa-exclamation-circle"></i> 
                                    <strong><?php echo $corso['daCorreggere']; ?></strong> compiti da correggere
                                </div>
                            <?php else: ?>
                                <div class="alert-mini stato-successo">
                                    <i class="fas fa-check"></i> Tutto corretto
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="footer-corso">
                            <a href="studenti.php?corso_id=<?php echo $corso['id']; ?>" class="btn-azione" title="Studenti">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="crea_compito.php?corso_id=<?php echo $corso['id']; ?>" class="btn-azione" title="Nuovo Compito">
                                <i class="fas fa-plus-circle"></i>
                            </a>
                            <a href="valuta_compiti.php?corso_id=<?php echo $corso['id']; ?>" class="btn-azione" title="Valuta">
                                <i class="fas fa-check-double"></i>
                            </a>
                            <a href="statistiche.php?corso_id=<?php echo $corso['id']; ?>" class="btn-azione" title="Statistiche">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="nessun-contenuto">
                <i class="fas fa-chalkboard-teacher"></i>
                <p>Non hai ancora creato nessun corso.</p>
                <a href="corsi.php" class="btn btn-primario mt-3">Crea il tuo primo corso</a>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php include '../inclusi/footer.php'; ?>