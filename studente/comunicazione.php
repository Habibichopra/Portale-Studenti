<?php

require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Comunicazione.php';
require_once '../classi/User.php';
require_once '../classi/Corso.php';

$studente_id = $_SESSION['user_id'];
$comunicazioneObj = new Comunicazione();
$userObj = new User();
$corsoObj = new Corso();

$messaggio_feedback = '';
$errore_feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'invia') {
        $destinatario_id = $_POST['destinatario_id'];
        $corso_id = !empty($_POST['corso_id']) ? $_POST['corso_id'] : null;
        $oggetto = trim($_POST['oggetto']);
        $messaggio = trim($_POST['messaggio']);

        if (!empty($destinatario_id) && !empty($oggetto) && !empty($messaggio)) {
            if ($comunicazioneObj->inviaComunicazione($studente_id, $destinatario_id, $corso_id, $oggetto, $messaggio)) {
                $messaggio_feedback = "Messaggio inviato con successo!";
            } else {
                $errore_feedback = "errore durante l'invio.";
            }
        } else {
            $errore_feedback = "Compila tutti i campi obbligatori.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'segna_letto') {
        $msg_id = $_POST['messaggio_id'];
        $comunicazioneObj->marcaComeLetto($msg_id);
        header("Location: comunicazioni.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'elimina') {
        $msg_id = $_POST['messaggio_id'];
        if($comunicazioneObj->deleteComunicazione($msg_id)) {
            $messaggio_feedback = "Messaggio eliminato.";
        }
    }

}

$messaggi = $comunicazioneObj->getComunicazioniByUser($studente_id);
$professori = $userObj->getAllProfessori();
$miei_corsi = $corsoObj->getCorsiByStudente($studente_id);


define('PAGE_TITLE', 'Comunicazioni');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-envelope"></i> Messaggi</h1>
            <p>Comunica con i docenti e ricevi avvisi importanti.</p>
        </div>
        <button class="btn btn-primario" onclick="apriChiudiForm()">
            <i class="fas fa-pen"></i> Scrivi Messaggio
        </button>
    </header>

    <?php if ($messaggio_feedback): ?>
        <div class="alert alert-successo"><?php echo $messaggio_feedback; ?></div>
    <?php endif; ?>
    <?php if ($errore_feedback): ?>
        <div class="alert alert-errore"><?php echo $errore_feedback; ?></div>
    <?php endif; ?>

    <div id="newmessaggioForm" class="scheda mb-5" style="display: none;">
        <div class="scheda-header">
            <h2>Nuovo Messaggio</h2>
            <button class="btn-icona" onclick="apriChiudForm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="body-scheda">
            <form method="POST" action="comunicazioni.php">
                <input type="hidden" name="action" value="invia">

                <div class="riga">
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Destinatario (Professore) *</label>
                            <select name="destinatario_id" class="controllo-form" required>
                                <option value="">-- Seleziona Docente --</option>
                                <?php foreach ($professori as $prof): ?>
                                    <option value="<?php echo $prof['id']; ?>">
                                        <?php echo htmlspecialchars($prof['cognome'] . ' ' . $prof['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Corso Correlato (Opzionale)</label>
                            <select name="corso_id" class="controllo-form">
                                <option value="">-- Nessun Corso --</option>
                                <?php foreach ($miei_corsi as $c): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        <?php echo htmlspecialchars($c['nome_corso']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="gruppo-form">
                    <label>Oggetto *</label>
                    <input type="text" name="oggetto" class="controllo-form" required placeholder="Es: Richiesta ricevimento">
                </div>
                <div class="gruppo-form">
                    <label>Messaggio *</label>
                    <textarea name="messaggio" class="controllo-form" rows="5" required placeholder="Scrivi qui il tuo messaggio..."></textarea>
                </div>

                <button type="submit" class="btn btn-avvenuto"><i class="fas fa-paper-plane"></i> Invia Messaggio</button>
            </form>

        </div>


    </div>

</div>

<script>
    function apriChiudiForm() {
    var form = document.getElementById('newmessaggioForm');
    if (form.style.display === "none") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
    }
</script>

<?php include '../inclusi/footer.php'; ?>