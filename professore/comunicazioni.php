<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Comunicazione.php';
require_once '../classi/User.php';
require_once '../classi/Corso.php';

$prof_id = $_SESSION['user_id'];
$comunicazioneObj = new Comunicazione();
$corsoObj = new Corso();

$database = Database::getInstance();
$db = $database->getConnection();

$messaggio_feedback = '';
$errore_feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action']) && $_POST['action'] === 'invia') {
        $destinatario_id = $_POST['destinatario_id'];
        $oggetto = trim($_POST['oggetto']);
        $messaggio = trim($_POST['messaggio']);
        $corso_id = !empty($_POST['corso_id']) ? $_POST['corso_id'] : null;

        if (!empty($destinatario_id) && !empty($oggetto) && !empty($messaggio)) {
            if ($comunicazioneObj->inviaComunicazione($prof_id, $destinatario_id, $corso_id, $oggetto, $messaggio)) {
                $messaggio_feedback = "Messaggio inviato correttamente.";
            } else {
                $errore_feedback = "errore durante l'invio del messaggio.";
            }
        } else {
            $errore_feedback = "Tutti i campi sono obbligatori.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'segna_letto') {
        $msg_id = $_POST['messaggio_id'];
        $comunicazioneObj->segnaComeLetto($msg_id);
        header("Location: comunicazioni.php");
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'elimina') {
        $msg_id = $_POST['messaggio_id'];
        if($comunicazioneObj->eliminaComunicazione($msg_id)) {
            $messaggio_feedback = "Conversazione eliminata.";
        }
    }
}

$messaggi = $comunicazioneObj->getComunicazioniByUser($prof_id);

$query_studenti = "SELECT DISTINCT u.id, u.nome, u.cognome, c.nome_corso, c.id as corso_id
                   FROM users u
                   JOIN iscrizioni i ON u.id = i.studente_id
                   JOIN corsi c ON i.corso_id = c.id
                   WHERE c.professore_id = :pid AND i.status = 'attivo'
                   ORDER BY c.nome_corso, u.cognome ASC";

$stmt = $db->prepare($query_studenti);
$stmt->bindParam(":pid", $prof_id);
$stmt->execute();
$miei_studenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

$studenti_per_corso = [];
foreach ($miei_studenti as $s) {
    $studenti_per_corso[$s['nome_corso']][] = $s;
}


$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);

define('PAGE_TITLE', 'Messaggi Studenti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-envelope-open-text"></i> Comunicazioni</h1>
            <p>Gestisci la corrispondenza con i tuoi studenti.</p>
        </div>
        <button class="btn btn-primario" onclick="toggleNewmessaggioForm()">
            <i class="fas fa-paper-plane"></i> Scrivi Messaggio
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
            <h2>Nuova Comunicazione</h2>
            <button class="btn-icona" onclick="toggleNewmessaggioForm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="body-scheda">
            <form method="POST" action="comunicazioni.php">
                <input type="hidden" name="action" value="invia">
                
                <div class="riga">
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Destinatario (Studente) *</label>
                            <select name="destinatario_id" class="controllo-form" required id="selectStudent" onchange="autoSelectCourse(this)">
                                <option value="">-- Seleziona Studente --</option>
                                <?php foreach ($studenti_per_corso as $nome_corso => $studenti): ?>
                                    <optgroup label="<?php echo htmlspecialchars($nome_corso); ?>">
                                        <?php foreach ($studenti as $stud): ?>
                                            <option value="<?php echo $stud['id']; ?>" data-corso="<?php echo $stud['corso_id']; ?>">
                                                <?php echo htmlspecialchars($stud['cognome'] . ' ' . $stud['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="colonna-meta">
                        <div class="gruppo-form">
                            <label>Corso di Riferimento</label>
                            <select name="corso_id" id="selectCourse" class="controllo-form">
                                <option value="">-- Generico --</option>
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
                    <input type="text" name="oggetto" class="controllo-form" required placeholder="Es: Riscontro esame, info tesi...">
                </div>

                <div class="gruppo-form">
                    <label>Messaggio *</label>
                    <textarea name="messaggio" class="controllo-form" rows="5" required placeholder="Scrivi qui..."></textarea>
                </div>

                <button type="submit" class="btn btn-avvenuto"><i class="fas fa-share-square"></i> Invia</button>
            </form>
        </div>
    </div>

    <section class="messaggios-list">
        <?php if (count($messaggi) > 0): ?>
            <?php foreach ($messaggi as $msg): ?>
                <div class="messaggio-card <?php echo ($msg['letto'] == 0) ? 'unread' : ''; ?>">
                    
                    <div class="msg-header" onclick="togglemessaggio(<?php echo $msg['id']; ?>)">
                        <div class="msg-avatar">
                            <?php 
            
                                echo strtoupper(substr($msg['nome_mittente'], 0, 1) . substr($msg['cognome_mittente'], 0, 1));
                            ?>
                        </div>
                        <div class="msg-preview">
                            <div class="msg-top">
                                <span class="nome-mittente">
                                    <?php echo htmlspecialchars($msg['nome_mittente'] . ' ' . $msg['cognome_mittente']); ?>
                                    <span class="badge-ruolo">Studente</span>
                                </span>
                                <span class="msg-data"><?php echo date('d/m/Y H:i', strtotime($msg['data_invio'])); ?></span>
                            </div>
                            <div class="msg-contenuto">
                                <?php if($msg['nome_corso']): ?>
                                    <span class="etichetta-codice-"><?php echo htmlspecialchars($msg['nome_corso']); ?></span>
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($msg['oggetto']); ?></strong>
                            </div>
                        </div>
                        <div class="msg-ingrandisci-icona">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>

                    <div class="msg-body" id="msg-body-<?php echo $msg['id']; ?>">
                        <hr class="separatore-light">
                        <p><?php echo nl2br(htmlspecialchars($msg['messaggio'])); ?></p>
                        
                        <div class="msg-azioni">
                            <?php if ($msg['letto'] == 0): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="segna_letto">
                                    <input type="hidden" name="messaggio_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-contorno btn-">Segna come letto</button>
                                </form>
                            <?php else: ?>
                                <span class="testp-successo text-"><i class="fas fa-check"></i> Letto</span>
                            <?php endif; ?>

                            <button class="btn btn-primario btn-" onclick="preparaRisposta('<?php echo $msg['mittente_id']; ?>', 'Re: <?php echo addslashes($msg['oggetto']); ?>', '<?php echo $msg['corso_id']; ?>')">
                                Rispondi
                            </button>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminare questo messaggio?');">
                                <input type="hidden" name="action" value="elimina">
                                <input type="hidden" name="messaggio_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" class="btn btn-pericolo btn-"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="nessun-contenuto">
                <i class="far fa-comments"></i>
                <p>Nessun messaggio ricevuto dagli studenti.</p>
            </div>
        <?php endif; ?>
    </section>

</div>

<script>
function toggleNewmessaggioForm() {
    var form = document.getElementById('newmessaggioForm');
    form.style.display = (form.style.display === "none") ? "block" : "none";
}

function togglemessaggio(id) {
    var body = document.getElementById('msg-body-' + id);
    var card = body.closest('.messaggio-card');
    if (body.style.display === "block") {
        body.style.display = "none";
        card.classList.remove('expanded');
    } else {
        body.style.display = "block";
        card.classList.add('expanded');
    }
}

function autoSelectCourse(selectObject) {
    var selectedOption = selectObject.options[selectObject.selectedIndex];
    var corsoId = selectedOption.getAttribute('data-corso');
    var courseSelect = document.getElementById('selectCourse');
    
    if (corsoId) {
        courseSelect.value = corsoId;
    }
}

function preparaRisposta(destinatarioId, oggetto, corsoId) {

    document.getElementById('newmessaggioForm').style.display = 'block';
    
    var selectStudent = document.getElementById('selectStudent');
    
    document.querySelector('input[name="oggetto"]').value = oggetto;
    if(corsoId) document.getElementById('selectCourse').value = corsoId;
    
    window.scrollTo(0, 0);
    alert("Compila il destinatario per rispondere a: " + oggetto);
}
</script>


<?php include '../inclusi/footer.php'; ?>