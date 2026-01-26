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