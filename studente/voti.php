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
</div>


<?php 
include '../inclusi/footer.php'; 
?>
