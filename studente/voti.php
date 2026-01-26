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

    </div>



</div>


<?php 
include '../inclusi/footer.php'; 
?>
