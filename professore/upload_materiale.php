<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Materiale.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$materialeObj = new Materiale();

$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);

$corso_selezionato_id = isset($_REQUEST['corso_id']) ? $_REQUEST['corso_id'] : '';

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'upload') {
        $corso_id = $_POST['corso_id'];
        $titolo = trim($_POST['titolo']);
        $descrizione = trim($_POST['descrizione']);
        $tipo = $_POST['tipo'];
        
        $is_mio = false;
        foreach($miei_corsi as $c) { if($c['id'] == $corso_id) $is_mio = true; }

        if (!$is_mio) {
            $errore = "Operazione non autorizzata su questo corso.";
        } elseif (empty($titolo) || empty($_FILES['file_materiale']['name'])) {
            $errore = "Titolo e File sono obbligatori.";
        } else {
            if ($materialeObj->caricaMateriale($corso_id, $titolo, $descrizione, $tipo, $_FILES['file_materiale'])) {
                $messaggio = "Materiale caricato con successo!";
            } else {
                $errore = "errore durante il caricamento. Controlla dimensioni o permessi.";
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $materiale_id = $_POST['materiale_id'];
        if ($materialeObj->eliminaMateriale($materiale_id)) {
            $messaggio = "File eliminato correttamente.";
        } else {
            $errore = "Impossibile eliminare il file.";
        }
    }
}

$lista_materiali = [];
if ($corso_selezionato_id) {
    $lista_materiali = $materialeObj->getMaterialiByCorso($corso_selezionato_id);
}

function getIcona($tipo) {
    switch ($tipo) {
        case 'pdf': return 'fa-file-pdf';
        case 'slide': return 'fa-file-powerpoint';
        case 'video': return 'fa-video';
        default: return 'fa-file-alt';
    }
}

define('PAGE_TITLE', 'Carica Materiali');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-cloud-upload-alt"></i> Materiale Didattico</h1>
            <p>Carica slide, dispense e risorse per i tuoi studenti.</p>
        </div>
        <a href="dashboard.php" class="btn btn-contorno">&larr; Dashboard</a>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><i class="fas fa-check"></i> <?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><i class="fas fa-exclamation-triangle"></i> <?php echo $errore; ?></div>
    <?php endif; ?>

    <div class="layout-diviso">
        
        <div>
            <div class="scheda">
                <div class="scheda-header">
                    <h2>Nuovo Caricamento</h2>
                </div>
                <div class="body-scheda">
                    
                    <?php if (count($miei_corsi) > 0): ?>
                        <form method="POST" action="upload_materiale.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload">
                            
                            <div class="gruppo-form">
                                <label>Corso *</label>
                                <select name="corso_id" class="controllo-form" required onchange="window.location.href='upload_materiale.php?corso_id='+this.value">
                                    <option value="">-- Seleziona Corso --</option>
                                    <?php foreach ($miei_corsi as $corso): ?>
                                        <option value="<?php echo $corso['id']; ?>" <?php echo ($corso_selezionato_id == $corso['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($corso['nome_corso']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="gruppo-form">
                                <label>Titolo Risorsa *</label>
                                <input type="text" name="titolo" class="controllo-form" required placeholder="Es: Slide Lezione 1">
                            </div>

                            <div class="riga">
                                <div class="colonna-meta">
                                    <div class="gruppo-form">
                                        <label>Tipo *</label>
                                        <select name="tipo" class="controllo-form">
                                            <option value="pdf">Documento PDF</option>
                                            <option value="slide">Slide / PowerPoint</option>
                                            <option value="video">Video / Link</option>
                                            <option value="altro">Altro (Zip, Txt)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="colonna-meta">
                                    <div class="gruppo-form">
                                        <label>File *</label>
                                        <input type="file" name="file_materiale" class="controllo-form-file" required>
                                    </div>
                                </div>
                            </div>

                            <div class="gruppo-form">
                                <label>Descrizione (Opzionale)</label>
                                <textarea name="descrizione" class="controllo-form" rows="2" placeholder="Breve descrizione del contenuto..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primario btn-blocco">
                                <i class="fas fa-upload"></i> Carica Online
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-errore">
                            Non hai ancora creato corsi. <a href="corsi.php">Crea un corso prima.</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="action-column">
            <div class="scheda">
                <div class="scheda-header">
                    <h3>File Caricati</h3>
                </div>
                <div class="body-scheda">
                    <?php if ($corso_selezionato_id): ?>
                        <?php if (count($lista_materiali) > 0): ?>
                            <ul class="file-list">
                                <?php foreach ($lista_materiali as $file): ?>
                                    <li class="file-item">
                                        <div class="file-icon">
                                            <i class="fas <?php echo getIcona($file['tipo']); ?>"></i>
                                        </div>
                                        <div class="file-info">
                                            <strong><?php echo htmlspecialchars($file['titolo']); ?></strong>
                                            <br>
                                            <small class="testo-disattivato"><?php echo date('d/m/Y', strtotime($file['data_upload'])); ?></small>
                                        </div>
                                        <div class="file-actions">
                                            <a href="<?php echo BASE_URL . $file['file_path']; ?>" target="_blank" class="btn-icona text-primary" title="Visualizza">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <form method="POST" action="upload_materiale.php" style="display:inline;" onsubmit="return confirm('Vuoi cancellare definitivamente questo file?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="materiale_id" value="<?php echo $file['id']; ?>">
                                                <input type="hidden" name="corso_id" value="<?php echo $corso_selezionato_id; ?>"> <button type="submit" class="btn-icona testo-pericolo" title="Elimina">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="testo-disattivato testo-centrato py-4">Nessun materiale caricato per questo corso.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="nessun-contenuto-small">
                            <i class="fas fa-arrow-left"></i>
                            <p>Seleziona un corso a sinistra per vedere i file gestiti.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>


<?php include '../inclusi/footer.php'; ?>