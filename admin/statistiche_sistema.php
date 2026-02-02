<?php

require_once '../config/config.php';
$required_ruolo = 'admin';
require_once '../inclusi/session_check.php';
require_once '../classi/Database.php';

$database = new Database();
$db = $database->getConnection();

$query_utenti = "SELECT ruolo, COUNT(*) as totale FROM users GROUP BY ruolo";
$stmt = $db->prepare($query_utenti);
$stmt->execute();
$dati_utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels_utenti = [];
$values_utenti = [];
foreach($dati_utenti as $riga) {
    $labels_utenti[] = ucfirst($riga['ruolo']);
    $values_utenti[] = $riga['totale'];
}

$query_popolari = "SELECT c.codice_corso, c.nome_corso, COUNT(i.id) as iscritti 
                   FROM corsi c 
                   LEFT JOIN iscrizioni i ON c.id = i.corso_id 
                   WHERE i.status = 'attivo' OR i.status IS NULL
                   GROUP BY c.id 
                   ORDER BY iscritti DESC 
                   LIMIT 5";
$stmt = $db->prepare($query_popolari);
$stmt->execute();
$corsi_popolari = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query_medie = "SELECT c.codice_corso, AVG(v.voto) as media_voto 
                FROM voti v 
                JOIN corsi c ON v.corso_id = c.id 
                GROUP BY c.id 
                ORDER BY media_voto DESC 
                LIMIT 10";
$stmt = $db->prepare($query_medie);
$stmt->execute();
$corsi_medie = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tot_consegne = $db->query("SELECT COUNT(*) FROM consegne")->fetchColumn();
$tot_files = $db->query("SELECT COUNT(*) FROM materiali")->fetchColumn();
$tot_voti = $db->query("SELECT COUNT(*) FROM voti")->fetchColumn();

define('PAGE_TITLE', 'Statistiche Sistema');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container layout-contenuto">
    
    <header class="header-pagina">
        <h1><i class="fas fa-chart-line"></i> Analytics di Sistema</h1>
        <p>Monitoraggio globale delle attività, iscrizioni e performance didattiche.</p>
    </header>

    <div class="griglia-statistiche mb-5">
        <div class="scheda-statistiche purple">
            <div class="icona-statistiche"><i class="fas fa-file-upload"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_consegne; ?></h3>
                <p>Compiti Consegnati</p>
            </div>
        </div>
        <div class="scheda-statistiche">
            <div class="icona-statistiche"><i class="fas fa-folder-open"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_files; ?></h3>
                <p>Materiali Didattici</p>
            </div>
        </div>
        <div class="scheda-statistiche green">
            <div class="icona-statistiche"><i class="fas fa-star"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_voti; ?></h3>
                <p>Voti Verbalizzati</p>
            </div>
        </div>
    </div>

    <div class="dashboard-main-griglia">
        
        <div class="scheda">
            <div class="scheda-header">
                <h3>Utenti per Ruolo</h3>
            </div>
            <div class="body-scheda">
                <div style="max-width: 300px; margin: 0 auto;">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>

        <div class="scheda">
            <div class="scheda-header">
                <h3>Top 5 Corsi (Iscritti)</h3>
            </div>
            <div class="body-scheda">
                <canvas id="courseChart"></canvas>
            </div>
        </div>

    </div>

    <div class="scheda mt-4">
        <div class="scheda-header">
            <h3>Media Voti per Corso</h3>
            <p class="testo-disattivato text-sm">Analisi della difficoltà dei corsi basata sulla media voti.</p>
        </div>
        <div class="body-scheda">
            <canvas id="gradesChart" height="80"></canvas>
        </div>
    </div>

</div>

<script>
    const ctxUser = document.getElementById('userChart').getContext('2d');
    new Chart(ctxUser, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labels_utenti); ?>,
            datasets: [{
                data: <?php echo json_encode($values_utenti); ?>,
                backgroundColor: ['#3498db', '#2ecc71', '#34495e'], // Blu, Verde, Scuro
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // --- 2. CONFIGURAZIONE CHART CORSI POPOLARI (BARRE ORIZZONTALI) ---
    <?php 
        $courseLabels = array_column($corsi_popolari, 'codice_corso');
        $courseData = array_column($corsi_popolari, 'iscritti');
        $courseNames = array_column($corsi_popolari, 'nome_corso'); // Per il tooltip
    ?>
    const ctxCourse = document.getElementById('courseChart').getContext('2d');
    new Chart(ctxCourse, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($courseLabels); ?>,
            datasets: [{
                label: 'Studenti Iscritti',
                data: <?php echo json_encode($courseData); ?>,
                backgroundColor: 'rgba(155, 89, 182, 0.6)', // Viola
                borderColor: 'rgba(155, 89, 182, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Barre orizzontali
            responsive: true,
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: {
                tooltip: {
                    callbacks: {
                        // Mostra il nome completo del corso al passaggio del mouse
                        afterLabel: function(context) {
                            var names = <?php echo json_encode($courseNames); ?>;
                            return names[context.dataIndex];
                        }
                    }
                }
            }
        }
    });

    <?php 
        $gradeLabels = array_column($corsi_medie, 'codice_corso');
        $gradeData = array_column($corsi_medie, 'media_voto');
    ?>
    const ctxGrades = document.getElementById('gradesChart').getContext('2d');
    new Chart(ctxGrades, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($gradeLabels); ?>,
            datasets: [{
                label: 'Media Voti',
                data: <?php echo json_encode($gradeData); ?>,
                backgroundColor: function(context) {
    
                    const value = context.raw;
                    if (value < 22) return 'rgba(231, 76, 60, 0.6)';
                    if (value > 27) return 'rgba(46, 204, 113, 0.6)';
                    return 'rgba(52, 152, 219, 0.6)';
                },
                borderColor: '#ccc',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { min: 18, max: 31 } 
            }
        }
    });
</script>

<?php include '../inclusi/footer.php'; ?>