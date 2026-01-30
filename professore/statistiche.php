<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Voto.php';
require_once '../classi/EsportatoreCSV.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$votoObj = new Voto();
$csvExporter = new EsportatoreCSV();

$db = Database::getInstance()->getConnection();

$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);
$corso_selezionato_id = isset($_GET['corso_id']) ? $_GET['corso_id'] : '';

$download_link = '';
if (isset($_POST['export_stats']) && $corso_selezionato_id) {
    $filename = $csvExporter->exportStatisticheCorso($corso_selezionato_id);
    if ($filename) {
        $download_link = BASE_URL . 'exportazioni/' . $filename;
    }
}

$stats_base = null;
$distribuzione_voti = []; 
$tipi_valutazione = [];   

if ($corso_selezionato_id) {
    $is_mio = false;
    foreach($miei_corsi as $c) { if($c['id'] == $corso_selezionato_id) $is_mio = true; }
    
    if($is_mio) {
        $stats_base = $votoObj->getStatisticheCorso($corso_selezionato_id);

        $query_dist = "SELECT ROUND(voto) as voto_intero, COUNT(*) as quantita 
                        FROM voti WHERE corso_id = :cid 
                        GROUP BY ROUND(voto) 
                        ORDER BY voto_intero ASC";
        $stmt = $db->prepare($query_dist);
        $stmt->bindParam(":cid", $corso_selezionato_id);
        $stmt->execute();
        
        for($i=18; $i<=31; $i++) { $distribuzione_voti[$i] = 0; }
        
        while($riga = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $distribuzione_voti[$riga['voto_intero']] = $riga['quantita'];
        }

        $query_type = "SELECT tipo_valutazione, COUNT(*) as quantita 
                        FROM voti WHERE corso_id = :cid 
                        GROUP BY tipo_valutazione";
        $stmt = $db->prepare($query_type);
        $stmt->bindParam(":cid", $corso_selezionato_id);
        $stmt->execute();
        $tipi_valutazione = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $corso_selezionato_id = ''; 
    }
}

define('PAGE_TITLE', 'Statistiche Corso');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-chart-pie"></i> Analisi e Report</h1>
            <p>Visualizza l'andamento della classe e distribuzioni dei voti.</p>
        </div>
        
        <?php if ($corso_selezionato_id): ?>
            <form method="POST">
                <button type="submit" name="export_stats" class="btn btn-avvenuto">
                    <i class="fas fa-download"></i> Scarica Report CSV
                </button>
            </form>
        <?php endif; ?>
    </header>

    <?php if ($download_link): ?>
        <div class="alert alert-successo">
            Report generato! <a href="<?php echo $download_link; ?>" class="alert-link" download>Clicca qui per scaricare</a>.
        </div>
    <?php endif; ?>

    <div class="scheda mb-4">
        <div class="body-scheda">
            <form method="GET">
                <label><strong>Analizza Corso:</strong></label>
                <select name="corso_id" class="controllo-form" onchange="this.form.submit()">
                    <option value="">-- Seleziona --</option>
                    <?php foreach ($miei_corsi as $corso): ?>
                        <option value="<?php echo $corso['id']; ?>" <?php echo ($corso_selezionato_id == $corso['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($corso['nome_corso']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($corso_selezionato_id && $stats_base): ?>
        
        <div class="griglia-statistiche mb-5">
            <div class="scheda-statistiche">
                <div class="icona-statistiche"><i class="fas fa-calculator"></i></div>
                <div class="info-statistiche">
                    <h3><?php echo number_format((float)$stats_base['media_corso'], 2); ?></h3>
                    <p>Media Classe</p>
                </div>
            </div>
            <div class="scheda-statistiche verde">
                <div class="icona-statistiche"><i class="fas fa-trophy"></i></div>
                <div class="info-statistiche">
                    <h3><?php echo $stats_base['voto_max'] ?? '-'; ?></h3>
                    <p>Voto Migliore</p>
                </div>
            </div>
            <div class="scheda-statistiche rosso">
                <div class="icona-statistiche"><i class="fas fa-exclamation"></i></div>
                <div class="info-statistiche">
                    <h3><?php echo $stats_base['voto_min'] ?? '-'; ?></h3>
                    <p>Voto Più Basso</p>
                </div>
            </div>
            <div class="scheda-statistiche viola">
                <div class="icona-statistiche"><i class="fas fa-users"></i></div>
                <div class="info-statistiche">
                    <h3><?php echo $stats_base['totale_voti']; ?></h3>
                    <p>Valutazioni Tot.</p>
                </div>
            </div>
        </div>

        <div class="dashboard-main-griglia">
            
            <div class="scheda larghezza-piena-mobile">
                <div class="scheda-header">
                    <h3>Distribuzione Voti</h3>
                </div>
                <div class="body-scheda">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>

            <div class="scheda larghezza-piena-mobile">
                <div class="scheda-header">
                    <h3>Tipologia Valutazioni</h3>
                </div>
                <div class="body-scheda">
                    <div style="max-width: 300px; margin: 0 auto;">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <script>
            const ctxGrade = document.getElementById('gradeChart').getContext('2d');
            new Chart(ctxGrade, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_keys($distribuzione_voti)); ?>,
                    datasets: [{
                        label: 'Numero Studenti',
                        data: <?php echo json_encode(array_values($distribuzione_voti)); ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.6)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            const ctxType = document.getElementById('typeChart').getContext('2d');
            
            <?php 
                $typeLabels = [];
                $typeData = [];
                foreach($tipi_valutazione as $t) {
                    $typeLabels[] = ucfirst($t['tipo_valutazione']);
                    $typeData[] = $t['quantita'];
                }
            ?>

            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($typeLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($typeData); ?>,
                        backgroundColor: ['#e74c3c', '#f1c40f', '#2ecc71', '#9b59b6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        </script>

    <?php elseif ($corso_selezionato_id): ?>
        <div class="nessun-contenuto">
            <p>Non ci sono ancora dati sufficienti per generare statistiche per questo corso.</p>
        </div>
    <?php else: ?>
        <div class="nessun-contenuto">
            <i class="fas fa-chart-bar"></i>
            <p>Seleziona un corso dal menu in alto per visualizzare l'analisi.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../inclusi/footer.php'; ?>