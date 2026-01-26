<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Materiale.php';

$studente_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$materialeObj = new Materiale();

$miei_corsi = $corsoObj->getCorsiByStudente($studente_id);

$corsi_map = [];
foreach ($miei_corsi as $c) {
    $corsi_map[$c['id']] = $c;
}

$corso_selezionato = isset($_GET['corso_id']) ? $_GET['corso_id'] : 'tutti';

$lista_materiali = [];

if ($corso_selezionato !== 'tutti') {
    
    if (!array_key_exists($corso_selezionato, $corsi_map)) {
        header("Location: materiali.php");
        exit;
    }
    
    $materiali_base = $materialeObj->getMaterialiByCorso($corso_selezionato);
    
    foreach ($materiali_base as $m) {
        $m['nome_corso'] = $corsi_map[$m['corso_id']]['nome_corso'];
        $m['codice_corso'] = $corsi_map[$m['corso_id']]['codice_corso'];
        $lista_materiali[] = $m;
    }

} else {

    foreach ($miei_corsi as $corso) {
        $mats = $materialeObj->getMaterialiByCorso($corso['id']);
        foreach ($mats as $m) {
            $m['nome_corso'] = $corso['nome_corso'];
            $m['codice_corso'] = $corso['codice_corso'];
            $lista_materiali[] = $m;
        }
    }
    
    usort($lista_materiali, function($a, $b) {
        return strtotime($b['data_upload']) - strtotime($a['data_upload']);
    });
}

function getIconaMateriale($tipo) {
    switch ($tipo) {
        case 'pdf': return '<i class="fas fa-file-pdf text-danger"></i>';
        case 'slide': return '<i class="fas fa-file-powerpoint text-warning"></i>';
        case 'video': return '<i class="fas fa-video text-info"></i>';
        case 'zip': return '<i class="fas fa-file-archive testo-disattivato"></i>';
        default: return '<i class="fas fa-file text-primary"></i>';
    }
}

define('PAGE_TITLE', 'Materiale Didattico');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-folder-open"></i> Materiale Didattico</h1>
            <p>Scarica slide, dispense e risorse caricate dai docenti.</p>
        </div>

        <div>
            <form method="GET" action="materiali.php">
                <select name="corso_id" onchange="this.form.submit()" class="form-select">
                    <option value="tutti">📚 Tutti i corsi</option>
                    <?php foreach ($miei_corsi as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($corso_selezionato == $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['nome_corso']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </header>

    <section>
        <?php if (count($lista_materiali) > 0): ?>
            <div class="griglia-materiali">
                <?php foreach ($lista_materiali as $file): ?>
                        <div class="scheda-materiali">
                            <div class="icona-grande-materiale">
                                <?php echo getIconaMateriale($file['tipo']); ?>
                            </div>
                            
                            <div class="body-materiale">
                                <span class="avviso-corso"><?php echo htmlspecialchars($file['codice_corso']); ?></span>
                                <h3><?php echo htmlspecialchars($file['titolo']); ?></h3>
                                <p class="testo-disattivato">
                                    <?php echo htmlspecialchars($file['nome_corso']); ?>
                                </p>
                                
                                <?php if (!empty($file['descrizione'])): ?>
                                    <p>
                                        <?php echo htmlspecialchars($file['descrizione']); ?>
                                    </p>
                                <?php endif; ?>

                                <div>
                                    <span><?php echo date('d/m/Y', strtotime($file['data_upload'])); ?></span>
                                    <span><?php echo strtoupper($file['tipo']); ?></span>
                                </div>
                            </div>

                            <div class="footer-materiale">
                                <a href="<?php echo BASE_URL . $file['file_path']; ?>" class="btn btn-contorno btn-blocco" target="_blank">
                                    <i class="fas fa-download"></i> Scarica / Visualizza
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

    
    </section>
</div>
<?php include '../inclusi/footer.php'; ?>