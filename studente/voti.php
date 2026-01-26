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

    $filename = $csvExporter->exportVotiStudente($studente_id);
    if ($filename) {
        // compit: creo il link per il download del file CSV
        $download_link = BASE_URL . 'esportazioni/' . $filename;
    }
}

$lista_voti = $votoObj->getVotiByStudente($studente_id);
$media_totale = $votoObj->calcolaMedia($studente_id);

$esami_superati = 0;
foreach ($lista_voti as $v) {
    if ($v['voto'] >= 18) {
        $esami_superati++;
    }
}

define('PAGE_TITLE', 'Il mio Libretto');
include '../inclusi/header.php';
include '../inclusi/nav.php';

?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-graduation-cap"></i> Libretto Voti</h1>
            <p>Monitora la tua media e l'andamento dei tuoi esami.</p>
        </div>

        <div>
            <form method="POST">
                <button type="submit" name="export_csv" class="btn btn-avvenuto">
                    <i class="fas fa-file-csv"></i> Esporta in CSV
                </button>
            </form>
        </div>
    </header>

    <?php if ($download_link): ?>
        <div class="alert alert-successo">
            <i class="fas fa-check"></i> File generato con successo! 
            <a href="<?php echo $download_link; ?>" download>Clicca qui per scaricare la tua pagella.</a>
        </div>
    <?php endif; ?>
    
    <div class="griglia-statistiche mb-5">
        <div class="scheda-statistiche">
            <div class="icona-statistiche"><i class="fas fa-calculator"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $media_totale; ?></h3>
                <p>Media Aritmetica</p>
            </div>
        </div>

        <div class="scheda-statistiche.verde">
            <div class="icona-statistiche"><i class="fas fa-check-double"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $esami_superati; ?></h3>
                <p>Esami Superati</p>
            </div>
        </div>

        <div class="scheda-statistiche.viola">
            <div class="icona-statistiche"><i class="fas fa-list-ol"></i></div>
            <div class="info-statistiche">
                <h3><?php echo count($lista_voti); ?></h3>
                <p>Valutazioni Totali</p>
            </div>
        </div>

    </div>

    <div class="dashboard-main-griglia">

        <section class="scheda larghezza-piena-mobile">
            <div class="scheda-header">
                <h2>Elenco Valutazioni</h2>
            </div>
            <div class="body-scheda">
                <?php if (count($lista_voti) > 0): ?>
                    <div class="tabella-responsive">
                        <table class="tabella-semplice tabella-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Corso</th>
                                    <th>Tipo</th>
                                    <th>Voto</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_voti as $voto): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($voto['data_voto'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($voto['nome_corso']); ?></strong>
                                            <br>
                                            <small class="testo-disattivato"><?php echo htmlspecialchars($voto['codice_corso']); ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            $icon = 'fas fa-pen';
                                            if ($voto['tipo_valutazione'] == 'esame') $icon = 'fas fa-university';
                                            if ($voto['tipo_valutazione'] == 'progetto') $icon = 'fas fa-laptop-code';
                                            ?>
                                            <i class="<?php echo $icon; ?> testo-disattivato"></i> 
                                            <?php echo ucfirst($voto['tipo_valutazione']); ?>
                                        </td>
                                        <td>
                                            <span class="etichetta-voto <?php echo ($voto['voto'] >= 24) ? 'alto' : (($voto['voto'] >= 18) ? 'medio' : 'basso'); ?>">
                                                <?php echo $voto['voto']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($voto['note'])): ?>
                                                <span>
                                                    <i class="far fa-comment-alt"></i> Note
                                                </span>
                                            <?php else: ?>
                                                <span class="testo-disattivato">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="nessun-contenuto">
                        <p>Non ci sono ancora voti registrati.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="scheda larghezza-piena-mobile">
            <div class="scheda-header">
                <h2>Andamento</h2>
            </div>
            <div class="body-scheda">
                <canvas id="votiChart"></canvas>
            </div>
        </section>

    </div>
</div>


<script>
    <?php
        $voti_cronologici = array_reverse($lista_voti);
        $nomi_corsi = [];
        $voti_conseguiti = [];
        
        foreach ($voti_cronologici as $v) {
            $nomi_corsi[] = substr($v['nome_corso'], 0, 15) . (strlen($v['nome_corso']) > 15 ? '...' : ''); 
            $voti_conseguiti[] = $v['voto'];
        }
    ?>

    const contestoGrafico = document.getElementById('votiChart').getContext('2d');
    const etichetteCorsi = <?php echo json_encode($nomi_corsi); ?>;
    const datiVoti = <?php echo json_encode($voti_conseguiti); ?>;

    const graficoAndamento = new Chart(contestoGrafico, {
        type: 'line',
        data: {
            labels: etichetteCorsi, 
            datasets: [{
                label: 'Voto Conseguito',
                data: datiVoti,     
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2980b9',
                pointHoverBackgroundColor: '#2980b9',
                pointHoverBorderColor: '#fff',
                tension: 0.3, 
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false 
                },
                tooltip: {
                    callbacks: {
                        label: function(contesto) {
                            let valore = contesto.parsed.y;
                            if (valore >= 31) {
                                return 'Voto: 30 e Lode';
                            }
                            return 'Voto: ' + valore;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 17, 
                    max: 32, 
                    ticks: {
                        stepSize: 1,
                        callback: function(valore, indice, valori) {
                            if (valore === 31) return '30L';
                            if (valore > 31) return '';
                            return valore;
                        }
                    },
                    grid: {
                        color: '#f0f0f0' 
                    }
                },
                x: {
                    grid: {
                        display: false 
                    }
                }
            }
        }
    });
</script>

<?php 
include '../inclusi/footer.php'; 
?>
