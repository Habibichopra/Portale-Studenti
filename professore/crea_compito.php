<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Compito.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$compitoObj = new Compito();

$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);


if (empty($miei_corsi)) {
    echo "Devi prima creare un corso per poter assegnare dei compiti. <a href='corsi.php'>Vai a Corsi</a>";
    exit;
}

$corso_selezionato = isset($_GET['corso_id']) ? $_GET['corso_id'] : '';

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $corso_id = $_POST['corso_id'];
    $titolo = trim($_POST['titolo']);
    $descrizione = trim($_POST['descrizione']);
    $data_scadenza = $_POST['data_scadenza'];
    $punti_max = intval($_POST['punti_max']);
    

    $corso_valido = false;
    foreach ($miei_corsi as $c) {
        if ($c['id'] == $corso_id) {
            $corso_valido = true;
            break;
        }
    }

    if (!$corso_valido) {
        $errore = "errore: Corso non valido o non autorizzato.";
    } elseif (empty($titolo) || empty($data_scadenza)) {
        $errore = "Titolo e Data di Scadenza sono obbligatori.";
    } else {
        
        $allegato_path = null;
        
        if (isset($_FILES['allegato']) && $_FILES['allegato']['error'] == 0) {
            $upload_dir = __DIR__ . "/../importazioni/compiti/";
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_ext = strtolower(pathinfo($_FILES['allegato']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'zip', 'txt', 'jpg', 'png'];
            
            if (in_array($file_ext, $allowed)) {
                $new_name = "compito_" . $corso_id . "_" . time() . "." . $file_ext;
                $destinazione = $upload_dir . $new_name;
                
                if (move_uploaded_file($_FILES['allegato']['tmp_name'], $destinazione)) {
                    $allegato_path = "importazioni/compiti/" . $new_name;
                } else {
                    $errore = "errore nel caricamento del file sul server.";
                }
            } else {
                $errore = "Formato file non supportato (ammessi: PDF, DOC, ZIP, IMG).";
            }
        }

        if (empty($errore)) {
            if ($compitoObj->creaCompito($corso_id, $titolo, $descrizione, $data_scadenza, $punti_max, $allegato_path)) {
                $messaggio = "Compito assegnato con successo!";
                $titolo = ''; 
                $descrizione = '';
            } else {
                $errore = "errore durante il salvataggio nel database.";
            }
        }
    }
}

define('PAGE_TITLE', 'Crea Compito');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-plus-circle"></i> Nuovo Compito</h1>
            <p>Assegna un'attività agli studenti del tuo corso.</p>
        </div>
        <a href="dashboard.php" class="btn btn-contorno">&larr; Torna alla Dashboard</a>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo">
            <i class="fas fa-check"></i> <?php echo $messaggio; ?>
            <a href="dashboard.php" class="alert-link">Torna alla home</a> o creane un altro.
        </div>
    <?php endif; ?>
    
    <?php if ($errore): ?>
        <div class="alert alert-errore">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $errore; ?>
        </div>
    <?php endif; ?>

    <div class="scheda">
        <div class="body-scheda">
            <form method="POST" action="crea_compito.php" enctype="multipart/form-data">
                
                <div class="gruppo-form">
                    <label>Seleziona Corso *</label>
                    <select name="corso_id" class="controllo-form" required>
                        <option value="">-- Scegli un corso --</option>
                        <?php foreach ($miei_corsi as $corso): ?>
                            <option value="<?php echo $corso['id']; ?>" 
                                <?php echo ($corso_selezionato == $corso['id']) ? 'selected' : ''; ?>>
                                [<?php echo htmlspecialchars($corso['codice_corso']); ?>] 
                                <?php echo htmlspecialchars($corso['nome_corso']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="riga">
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Titolo del Compito *</label>
                            <input type="text" name="titolo" class="controllo-form" required 
                                   placeholder="Es: Esercizi di Analisi I" 
                                   value="<?php echo isset($titolo) ? htmlspecialchars($titolo) : ''; ?>">
                        </div>
                    </div>
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Punteggio Massimo (Default: 30 o 100)</label>
                            <input type="number" name="punti_max" class="controllo-form" value="30" min="1" max="100">
                        </div>
                    </div>
                </div>

                <div class="gruppo-form">
                    <label>Descrizione e Istruzioni</label>
                    <textarea name="descrizione" class="controllo-form" rows="6" 
                              placeholder="Descrivi cosa devono fare gli studenti..."><?php echo isset($descrizione) ? htmlspecialchars($descrizione) : ''; ?></textarea>
                </div>

                <div class="riga">
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Data e Ora di Scadenza *</label>
                            <input type="datetime-local" name="data_scadenza" class="controllo-form" required
                                   min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Allegato (PDF, DOC, ZIP) - Opzionale</label>
                            <input type="file" name="allegato" class="controllo-form-file">
                            <small class="testo-disattivato">Carica una traccia o materiale utile.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primario btn-large">
                        <i class="fas fa-save"></i> Assegna Compito
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php include '../inclusi/footer.php'; ?>