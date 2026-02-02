<?php
require_once '../config/config.php';
$required_ruolo = 'admin';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/User.php';

$corsoObj = new Corso();
$userObj = new User();

$lista_professori = $userObj->getAllProfessori();

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['azione']) && $_POST['azione'] === 'crea') {
        $nome = trim($_POST['nome_corso']);
        $codice = trim($_POST['codice_corso']);
        $anno = $_POST['anno_accademico'];
        $crediti = intval($_POST['crediti']);
        $prof_id = $_POST['professore_id']; 
        $descrizione = trim($_POST['descrizione']);

        if (!empty($nome) && !empty($codice)) {
            if ($corsoObj->createCorso($nome, $codice, $descrizione, $anno, $prof_id, $crediti)) {
                $messaggio = "Corso creato e assegnato con successo.";
            } else {
                $errore = "errore: Codice corso probabilmente già esistente.";
            }
        } else {
            $errore = "Compila i campi obbligatori.";
        }
    }

    elseif (isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
        $id = $_POST['corso_id'];
        $prof_id = $_POST['professore_id'];
        
        $data = [
            'nome_corso' => trim($_POST['nome_corso']),
            'codice_corso' => trim($_POST['codice_corso']),
            'anno_accademico' => $_POST['anno_accademico'],
            'crediti' => intval($_POST['crediti']),
            'descrizione' => trim($_POST['descrizione'])
        ];

        if ($corsoObj->aggiornaCorso($id, $data)) {
            $db = (new Database())->getConnection();
            $q = "UPDATE corsi SET professore_id = :pid WHERE id = :cid";
            $stmt = $db->prepare($q);
            $stmt->bindParam(':pid', $prof_id);
            $stmt->bindParam(':cid', $id);
            $stmt->execute();

            $messaggio = "Corso aggiornato correttamente.";
        } else {
            $errore = "errore durante l'aggiornamento.";
        }
    }

    elseif (isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
        $id = $_POST['corso_id'];
        if ($corsoObj->eliminaCorso($id)) {
            $messaggio = "Corso eliminato dal sistema.";
        } else {
            $errore = "Impossibile eliminare il corso.";
        }
    }
}

$view = isset($_GET['view']) ? $_GET['view'] : 'lista';
$modifica_corso = null;

if ($view === 'modifica' && isset($_GET['id'])) {
    $modifica_corso = $corsoObj->getCorsoById($_GET['id']);
}

$lista_corsi = $corsoObj->getAllCorsi();

define('PAGE_TITLE', 'Gestione Corsi Globale');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-layer-group"></i> Gestione Corsi</h1>
            <p>Amministrazione globale degli insegnamenti e assegnazione cattedre.</p>
        </div>
        <?php if ($view === 'lista'): ?>
            <a href="gestione_corsi.php?view=crea" class="btn btn-primario">
                <i class="fas fa-plus"></i> Nuovo Corso
            </a>
        <?php else: ?>
            <a href="gestione_corsi.php" class="btn btn-contorno">
                &larr; Annulla
            </a>
        <?php endif; ?>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><?php echo $errore; ?></div>
    <?php endif; ?>

    <?php if ($view === 'crea' || $view === 'modifica'): ?>
        <div class="scheda">
            <div class="scheda-header">
                <h2><?php echo ($view === 'modifica') ? 'Modifica Corso' : 'Crea Nuovo Corso'; ?></h2>
            </div>
            <div class="body-scheda">
                <form method="POST" action="gestione_corsi.php">
                    <input type="hidden" name="azione" value="<?php echo $view; ?>">
                    <?php if ($edit_corso): ?>
                        <input type="hidden" name="corso_id" value="<?php echo $edit_corso['id']; ?>">
                    <?php endif; ?>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Nome Corso *</label>
                                <input type="text" name="nome_corso" class="controllo-form" required 
                                       value="<?php echo $edit_corso ? htmlspecialchars($edit_corso['nome_corso']) : ''; ?>">
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Codice Univoco *</label>
                                <input type="text" name="codice_corso" class="controllo-form" required 
                                       value="<?php echo $edit_corso ? htmlspecialchars($edit_corso['codice_corso']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Professore Titolare *</label>
                                <select name="professore_id" class="controllo-form" required>
                                    <option value="">-- Seleziona Docente --</option>
                                    <?php foreach ($lista_professori as $prof): ?>
                                        <option value="<?php echo $prof['id']; ?>" 
                                            <?php echo ($edit_corso && $edit_corso['professore_id'] == $prof['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['cognome'] . ' ' . $prof['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Anno Accademico *</label>
                                <input type="text" name="anno_accademico" class="controllo-form" placeholder="es. 2023/2024" required
                                       value="<?php echo $edit_corso ? htmlspecialchars($edit_corso['anno_accademico']) : date('Y').'/'.(date('Y')+1); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="gruppo-form">
                        <label>Crediti (CFU)</label>
                        <input type="number" name="crediti" class="controllo-form" min="1" max="18" value="<?php echo $edit_corso ? $edit_corso['crediti'] : 6; ?>">
                    </div>

                    <div class="gruppo-form">
                        <label>Descrizione</label>
                        <textarea name="descrizione" class="controllo-form" rows="4"><?php echo $edit_corso ? htmlspecialchars($edit_corso['descrizione']) : ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primario">
                        <i class="fas fa-save"></i> Salva Corso
                    </button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="scheda">
            <div class="tabella-responsive">
                <table class="tabella-semplice tabella-hover">
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Materia</th>
                            <th>Professore Assegnato</th>
                            <th>CFU</th>
                            <th class="testo-destra">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_corsi as $c): ?>
                            <tr>
                                <td><span class="etichetta-codice"><?php echo htmlspecialchars($c['codice_corso']); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($c['nome_corso']); ?></strong>
                                    <br><small class="testo-disattivato"><?php echo htmlspecialchars($c['anno_accademico']); ?></small>
                                </td>
                                <td>
                                    <?php if ($c['prof_nome']): ?>
                                        <i class="fas fa-chalkboard-teacher testo-disattivato"></i> 
                                        <?php echo htmlspecialchars($c['prof_cognome'] . ' ' . $c['prof_nome']); ?>
                                    <?php else: ?>
                                        <span class="testo-pericolo">Non assegnato</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $c['crediti']; ?></td>
                                <td class="testo-destra">
                                    <a href="gestione_corsi.php?view=modifica&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-contorno">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="gestione_corsi.php" style="display:inline;" onsubmit="return confirm('Eliminare questo corso? Verranno cancellati tutti i dati associati!');">
                                        <input type="hidden" name="azione" value="elimina">
                                        <input type="hidden" name="corso_id" value="<?php echo $c['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-pericolo">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../inclusi/footer.php'; ?>