<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Compito.php';
require_once '../classi/Consegna.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$compitoObj = new Compito();
$consegnaObj = new Consegna();

$messaggio = isset($_GET['msg']) ? $_GET['msg'] : '';
$errore = isset($_GET['err']) ? $_GET['err'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salva_voto') {
    $consegna_id = intval($_POST['consegna_id']);
    $voto = floatval($_POST['voto']);
    $feedback = trim($_POST['feedback']);
    $compito_id_redirect = intval($_POST['compito_id']);

    $checkTask = $compitoObj->getCompitoById($compito_id_redirect);
    $max_punti = $checkTask ? $checkTask['punti_max'] : 100;

    if ($voto >= 0 && $voto <= $max_punti) {
        if ($consegnaObj->valutaConsegna($consegna_id, $voto, $feedback)) {
            header("Location: valuta_compiti.php?compito_id=" . $compito_id_redirect . "&msg=" . urlencode("Valutazione salvata con successo."));
            exit;
        } else {
            $errore = "errore durante il salvataggio nel database.";
        }
    } else {
        $errore = "Il voto deve essere compreso tra 0 e $max_punti.";
    }
}

$compito_id = isset($_GET['compito_id']) ? intval($_GET['compito_id']) : null;
$filtro_corso = isset($_GET['corso_id']) ? intval($_GET['corso_id']) : null;

if(isset($_GET['corso_id']) && !isset($_GET['compito_id'])) {
    $compito_id = null;
}

$task = null;
$lista_consegne = [];

if ($compito_id) {
    $task = $compitoObj->getCompitoById($compito_id);
    
    if ($task) {
        $lista_consegne = $consegnaObj->getConsegneByCompito($compito_id);
    } else {
        $errore = "Compito non trovato.";
        $compito_id = null; 
    }
} 

if (!$compito_id) {
    $miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);
    $lista_compiti = [];
    
    foreach ($miei_corsi as $corso) {
        if ($filtro_corso && $corso['id'] != $filtro_corso) continue;

        $tasks = $compitoObj->getCompitiByCorso($corso['id']);
        foreach ($tasks as $t) {
            // Calcolo statistiche rapide
            $consegne = $consegnaObj->getConsegneByCompito($t['id']);
            $da_correggere = 0;
            $totali = count($consegne);
            
            foreach ($consegne as $c) {
                if ($c['voto'] === null && ($c['stato'] == 'consegnato' || $c['stato'] == 'in_ritardo')) {
                    $da_correggere++;
                }
            }
            
            $t['da_correggere'] = $da_correggere;
            $t['totali'] = $totali;
            $t['nome_corso'] = $corso['nome_corso'];
            $t['codice_corso'] = $corso['codice_corso'];
            $lista_compiti[] = $t;
        }
    }
}

define('PAGE_TITLE', 'Valutazione Compiti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <?php if ($task): ?>
        <header class="header-pagina flex-header">
            <div>
                <a href="valuta_compiti.php<?php echo $filtro_corso ? '?corso_id='.$filtro_corso : ''; ?>" class="back-link mb-2 d-block">
                    &larr; Torna alla lista
                </a>
                <h1><i class="fas fa-check-double"></i> Valutazione: <?php echo htmlspecialchars($task['titolo']); ?></h1>
                <p class="testo-disattivato">
                    Corso: <strong><?php echo htmlspecialchars($task['nome_corso']); ?></strong> | 
                    Max Punti: <span class="badge badge-secondary"><?php echo $task['punti_max']; ?></span>
                </p>
            </div>
        </header>

        <?php if ($messaggio): ?>
            <div class="alert alert-successo fade-in"><?php echo htmlspecialchars($messaggio); ?></div>
        <?php endif; ?>
        <?php if ($errore): ?>
            <div class="alert alert-errore fade-in"><?php echo htmlspecialchars($errore); ?></div>
        <?php endif; ?>

        <div class="scheda">
            <div class="tabella-responsive">
                <?php if (count($lista_consegne) > 0): ?>
                    <table class="tabella-semplice tabella-hover">
                        <thead>
                            <tr>
                                <th>Studente</th>
                                <th>Data Consegna</th>
                                <th>Allegato</th>
                                <th>Stato</th>
                                <th>Voto</th>
                                <th class="testo-destra">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista_consegne as $riga): ?>
                                <?php 
                                    $is_late = ($riga['stato'] == 'in_ritardo');
                                    $data_consegna = date('d/m/Y H:i', strtotime($riga['data_consegna']));
                                    $has_file = !empty($riga['file_consegna']);
                                ?>
                                <tr class="<?php echo $is_late ? 'riga-warning-light' : ''; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2"><?php echo strtoupper(substr($riga['nome'],0,1).substr($riga['cognome'],0,1)); ?></div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($riga['cognome'] . ' ' . $riga['nome']); ?></strong><br>
                                                <small class="testo-disattivato"><?php echo htmlspecialchars($riga['matricola']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $data_consegna; ?>
                                        <?php if($is_late): ?>
                                            <div class="testo-pericolo text-xs font-weight-bold"><i class="fas fa-clock"></i> In Ritardo</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($has_file): ?>
                                            <a href="<?php echo BASE_URL . $riga['file_consegna']; ?>" target="_blank" class="btn-link" title="Scarica File">
                                                <i class="fas fa-file-download fa-lg"></i> Scarica
                                            </a>
                                        <?php else: ?>
                                            <span class="testo-disattivato text-xs">Nessun file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($riga['voto'] !== null): ?>
                                            <span class="segno-stato stato-avvenuto">Valutato</span>
                                        <?php else: ?>
                                            <span class="segno-stato stato-allerta">Da Correggere</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if ($riga['voto'] !== null) {
                                                $classVoto = ($riga['voto'] >= ($task['punti_max']*0.6)) ? 'testo-successo' : 'testo-pericolo';
                                                echo "<strong class='$classVoto'>" . $riga['voto'] . "</strong><span class='testo-disattivato'>/" . $task['punti_max'] ."</span>"; 
                                            } else {
                                                echo "-";
                                            }
                                        ?>
                                    </td>
                                    <td class="testo-destra">
                                        <button class="btn btn-sm btn-primario" 
                                                onclick="apriModalValutazione(
                                                    <?php echo $riga['id']; ?>, 
                                                    '<?php echo addslashes($riga['nome'] . ' ' . $riga['cognome']); ?>',
                                                    '<?php echo $riga['voto']; ?>',
                                                    '<?php echo addslashes(preg_replace( "/\r|\n/", " ", $riga['feedback_professore'] ?? '' )); ?>'
                                                )">
                                            <i class="fas fa-marker"></i> Valuta
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="nessun-contenuto testo-centrato py-5">
                        <img src="../assets/images/empty_box.svg" alt="Empty" style="width: 150px; opacity: 0.5; margin-bottom: 20px;">
                        <h3>Nessuna consegna</h3>
                        <p class="testo-disattivato">Gli studenti non hanno ancora caricato file per questo compito.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        
        <header class="header-pagina">
            <h1><i class="fas fa-tasks"></i> Compiti da Valutare</h1>
            <p class="testo-disattivato">Seleziona un'attività per visualizzare e correggere le consegne degli studenti.</p>
        </header>

        <div class="scheda">
            <?php if (count($lista_compiti) > 0): ?>
                <table class="tabella-semplice tabella-hover">
                    <thead>
                        <tr>
                            <th>Corso</th>
                            <th>Compito</th>
                            <th>Scadenza</th>
                            <th class="testo-centrato">Stato Correzioni</th>
                            <th class="testo-destra">Azione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_compiti as $t): ?>
                            <tr>
                                <td><span class="etichetta-codice"><?php echo htmlspecialchars($t['codice_corso']); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($t['titolo']); ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($t['data_scadenza'])); ?></td>
                                <td class="testo-centrato">
                                    <?php if ($t['da_correggere'] > 0): ?>
                                        <span class="badge badge-danger px-3 py-2"><?php echo $t['da_correggere']; ?> da valutare</span>
                                    <?php elseif ($t['totali'] > 0): ?>
                                        <span class="testo-successo"><i class="fas fa-check-circle"></i> Tutto fatto</span>
                                    <?php else: ?>
                                        <span class="testo-disattivato">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="testo-destra">
                                    <a href="valuta_compiti.php?compito_id=<?php echo $t['id']; ?>" class="btn btn-sm btn-contorno">
                                        Gestisci &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="nessun-contenuto testo-centrato">
                    <p>Non ci sono compiti assegnati nei tuoi corsi attuali.</p>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<?php if ($task): ?>
<div id="modalValutazione" class="modal-overlay" style="display: none;">
    <div class="modal-content animate-pop">
        <div class="modal-header">
            <h3 class="m-0">Valutazione: <span id="modalStudenteName" class="text-primary"></span></h3>
            <span class="close-modal" onclick="chiudiModal()">&times;</span>
        </div>
        
        <form method="POST" action="valuta_compiti.php">
            <input type="hidden" name="action" value="salva_voto">
            <input type="hidden" name="compito_id" value="<?php echo $task['id']; ?>">
            <input type="hidden" name="consegna_id" id="modalConsegnaId">
            
            <div class="modal-body">
                <div class="riga">
                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold">Voto (Max: <?php echo $task['punti_max']; ?>)</label>
                        <input type="number" name="voto" id="modalVoto" class="controllo-form" step="0.1" min="0" max="<?php echo $task['punti_max']; ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold">Feedback / Commento</label>
                        <textarea name="feedback" id="modalFeedback" class="controllo-form" rows="5" placeholder="Inserisci qui i commenti per lo studente..."></textarea>
                        <small class="testo-disattivato">Lo studente riceverà una notifica con questo commento.</small>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-testo testo-disattivato" onclick="chiudiModal()">Annulla</button>
                <button type="submit" class="btn btn-primario px-4">Salva Valutazione</button>
            </div>
        </form>
    </div>
</div>

<script>
function apriModalValutazione(id, nome, voto, feedback) {
    document.getElementById('modalConsegnaId').value = id;
    document.getElementById('modalStudenteName').innerText = nome;
    document.getElementById('modalVoto').value = (voto && voto !== '') ? voto : '';
    document.getElementById('modalFeedback').value = feedback;
    document.getElementById('modalValutazione').style.display = 'flex';
}

function chiudiModal() {
    document.getElementById('modalValutazione').style.display = 'none';
}

window.onclick = function(event) {
    var modal = document.getElementById('modalValutazione');
    if (event.target == modal) {
        chiudiModal();
    }
}
</script>

<?php endif; ?>

<?php include '../inclusi/footer.php'; ?>