<?php

require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classu/Compito.php';
require_once '../classi/Consegna.php';

if (!isset($_GET['id'])) {
    header("Location: compiti.php"); 
    exit;
}

$compito_id = $_GET['id'];
$studente_id = $_SESSION['user_id'];
$compitoObj = new Compito();
$consegnaObj = new Consegna();

$task = $compitoObj->getCompitoById($compito_id);
if (!$task) {
    die("Compito non trovato.");
}

$consegne_studente = $consegnaObj->getConsegneByStudente($studente_id);
$consegna_esistente = null;

foreach ($consegne_studente as $c) {
    if ($c['compito_id'] == $compito_id) {
        $consegna_esistente = $c;
        break;
    }
}

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_consegna'])) {
    
    $data_scadenza = new DateTime($task['data_scadenza']);
    $adesso = new DateTime();
    
    if ($adesso > $data_scadenza) {
        $errore = "Tempo scaduto! Non puoi più consegnare questo compito.";
    } elseif ($consegna_esistente) {
        $errore = "Hai già effettuato una consegna per questo compito.";
    } else {
        $note = $_POST['note'] ?? '';
        $file = $_FILES['file_consegna'];

        if ($consegnaObj->consegnaCompito($compito_id, $studente_id, $file, $note)) {
            header("Location: consegna.php?id=" . $compito_id . "&success=1");
            exit;
        } else {
            $errore = "errore durante il caricamento. Controlla il formato del file.";
        }
    }
}

if (isset($_GET['success'])) {
    $messaggio = "Compito consegnato con successo!";

    $consegne_studente = $consegnaObj->getConsegneByStudente($studente_id);
    foreach ($consegne_studente as $c) {
        if ($c['compito_id'] == $compito_id) {
            $consegna_esistente = $c;
            break;
        }
    }
}

$scadenza = new DateTime($task['data_scadenza']);
$oggi = new DateTime();
$is_scaduto = ($oggi > $scadenza);

define('PAGE_TITLE', 'Dettaglio Compito');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <a href="compiti.php">&larr; Torna ai Compiti</a>

    <div class="layout-diviso">
        
        <div>
            <header>
                <span class="avviso-corso"><?php echo htmlspecialchars($task['nome_corso']); ?></span>
                <h1><?php echo htmlspecialchars($task['titolo']); ?></h1>
                
                <div class="data-tasks">
                    <p>
                        <i class="far fa-calendar-plus"></i> Assegnato: 
                        <strong><?php echo date('d/m/Y', strtotime($task['data_assegnazione'])); ?></strong>
                    </p>
                    <p class="<?php echo $is_scaduto ? 'testo-pericolo' : ''; ?>">
                        <i class="far fa-clock"></i> Scadenza: 
                        <strong><?php echo $scadenza->format('d/m/Y H:i'); ?></strong>
                    </p>
                </div>
            </header>

            <div>
                <h3>Descrizione</h3>
                <p><?php echo nl2br(htmlspecialchars($task['descrizione'])); ?></p>
                
                <?php if (!empty($task['allegato'])): ?>
                    <div>
                        <i class="fas fa-paperclip"></i>
                        <span>Allegato del professore:</span>
                        <a href="<?php echo BASE_URL . $task['allegato']; ?>" target="_blank" class="btn-testo">Scarica File</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <?php if ($messaggio): ?>
                <div class="alert alert-successo"><?php echo $messaggio; ?></div>
            <?php endif; ?>
            
            <?php if ($errore): ?>
                <div class="alert alert-errore"><?php echo $errore; ?></div>
            <?php endif; ?>

            <?php if ($consegna_esistente): ?>
            <div class="segno-stato <?php echo ($consegna_esistente['stato'] == 'valutato') ? 'stato-valutato' : 'status-pending'; ?>">

            <div>
                <h2><i class="fas fa-check-circle"></i> Stato: <?php echo ucfirst($consegna_esistente['stato']); ?></h2>
                <span>Inviato il <?php echo date('d/m/Y H:i', strtotime($consegna_esistente['data_consegna'])); ?></span>
            </div>

            <div>
                <p><strong>Il tuo file:</strong></p>
                <a href="<?php echo BASE_URL . $consegna_esistente['file_consegna']; ?>" target="_blank">
                    <i class="fas fa-file-alt"></i> Scarica il tuo elaborato
                </a>
                
                <?php if(!empty($consegna_esistente['note_studente'])): ?>
                    <p class="mt-2"><strong>Le tue note:</strong></p>
                    <p>"<?php echo htmlspecialchars($consegna_esistente['note_studente']); ?>"</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include '../inclusi/footer.php'; ?>