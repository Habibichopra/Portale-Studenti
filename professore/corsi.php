<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';
require_once '../classi/Corso.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['cmd']) && $_POST['cmd'] === 'create') {
        $nome = trim($_POST['nome_corso']);
        $codice = trim($_POST['codice_corso']);
        $anno = trim($_POST['anno_accademico']);
        $crediti = intval($_POST['crediti']);
        $descrizione = trim($_POST['descrizione']);


        if (!empty($nome) && !empty($codice) && !empty($anno)) {
            if ($corsoObj->createCorso($nome, $codice, $descrizione, $anno, $prof_id, $crediti)) {
                $messaggio = "Corso creato con successo!";
            } else {
                $errore = "errore: Probabilmente il Codice Corso esiste già.";
            }
        } else {
            $errore = "Tutti i campi obbligatori devono essere compilati.";
        }
    }

    elseif (isset($_POST['cmd']) && $_POST['cmd'] === 'edit') {
        $id_corso = $_POST['corso_id'];
        $data = [
            'nome_corso' => trim($_POST['nome_corso']),
            'codice_corso' => trim($_POST['codice_corso']),
            'anno_accademico' => trim($_POST['anno_accademico']),
            'crediti' => intval($_POST['crediti']),
            'descrizione' => trim($_POST['descrizione'])
        ];
        
        if ($corsoObj->aggiornaCorso($id_corso, $data)) {
            $messaggio = "Corso aggiornato correttamente.";
        } else {
            $errore = "Impossibile aggiornare il corso.";
        }
    }

    elseif (isset($_POST['cmd']) && $_POST['cmd'] === 'delete') {
        $id_corso = $_POST['corso_id'];

        $corso_check = $corsoObj->getCorsoById($id_corso);
        if ($corso_check && $corso_check['professore_id'] == $prof_id) {
            if ($corsoObj->eliminaCorso($id_corso)) {
                $messaggio = "Corso eliminato definitivamente.";
            } else {
                $errore = "errore durante l'eliminazione.";
            }
        } else {
            $errore = "Non hai i permessi per eliminare questo corso.";
        }
    }
}


$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$corso_edit = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $corso_edit = $corsoObj->getCorsoById($_GET['id']);
    if (!$corso_edit || $corso_edit['professore_id'] != $prof_id) {
        header("Location: corsi.php");
        exit;
    }
}

define('PAGE_TITLE', 'Gestione Corsi');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-chalkboard"></i> Gestione Corsi</h1>
            <p>Crea, modifica ed elimina i tuoi insegnamenti.</p>
        </div>
        
        <?php if ($action === 'list'): ?>
            <a href="corsi.php?action=create" class="btn btn-primario">
                <i class="fas fa-plus"></i> Aggiungi Corso
            </a>
        <?php else: ?>
            <a href="corsi.php" class="btn btn-contorno">
                &larr; Torna alla lista
            </a>
        <?php endif; ?>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><?php echo $errore; ?></div>
    <?php endif; ?>

    <?php if ($action === 'create' || $action === 'edit'): ?>
        <div class="scheda">
            <div class="scheda-header">
                <h2><?php echo ($action === 'edit') ? 'Modifica Corso' : 'Nuovo Corso'; ?></h2>
            </div>
            <div class="body-scheda">
                <form method="POST" action="corsi.php">
                    <input type="hidden" name="cmd" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="corso_id" value="<?php echo $corso_edit['id']; ?>">
                    <?php endif; ?>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Nome Corso *</label>
                                <input type="text" name="nome_corso" class="controllo-form" required 
                                       value="<?php echo $corso_edit ? htmlspecialchars($corso_edit['nome_corso']) : ''; ?>">
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Codice Univoco (es. INF-01) *</label>
                                <input type="text" name="codice_corso" class="controllo-form" required 
                                       value="<?php echo $corso_edit ? htmlspecialchars($corso_edit['codice_corso']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Anno Accademico *</label>
                                <select name="anno_accademico" class="controllo-form">
                                    <?php 
                                    $anno_corrente = date('Y');
                                    $selected = $corso_edit ? $corso_edit['anno_accademico'] : ($anno_corrente . '/' . ($anno_corrente+1));
                                    ?>
                                    <option value="<?php echo ($anno_corrente-1).'/'.$anno_corrente; ?>" <?php echo $selected == ($anno_corrente-1).'/'.$anno_corrente ? 'selected' : ''; ?>>
                                        <?php echo ($anno_corrente-1).'/'.$anno_corrente; ?>
                                    </option>
                                    <option value="<?php echo $anno_corrente.'/'.($anno_corrente+1); ?>" <?php echo $selected == $anno_corrente.'/'.($anno_corrente+1) ? 'selected' : ''; ?>>
                                        <?php echo $anno_corrente.'/'.($anno_corrente+1); ?>
                                    </option>
                                    <option value="<?php echo ($anno_corrente+1).'/'.($anno_corrente+2); ?>" <?php echo $selected == ($anno_corrente+1).'/'.($anno_corrente+2) ? 'selected' : ''; ?>>
                                        <?php echo ($anno_corrente+1).'/'.($anno_corrente+2); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Crediti (CFU) *</label>
                                <input type="number" name="crediti" class="controllo-form" min="1" max="18" required
                                       value="<?php echo $corso_edit ? $corso_edit['crediti'] : '6'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="gruppo-form">
                        <label>Descrizione</label>
                        <textarea name="descrizione" class="controllo-form" rows="5"><?php echo $corso_edit ? htmlspecialchars($corso_edit['descrizione']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primario">
                            <i class="fas fa-save"></i> <?php echo ($action === 'edit') ? 'Salva Modifiche' : 'Crea Corso'; ?>
                        </button>
                        <a href="corsi.php" class="btn btn-testo">Annulla</a>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <?php 
        $lista_corsi = $corsoObj->getCorsiByProfessore($prof_id);
        ?>
        
        <?php if (count($lista_corsi) > 0): ?>
            <div class="scheda">
                <div class="tabella-responsive">
                    <table class="tabella-semplice tabella-hover">
                        <thead>
                            <tr>
                                <th>Codice</th>
                                <th>Corso</th>
                                <th>Anno</th>
                                <th>CFU</th>
                                <th class="te">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista_corsi as $corso): ?>
                                <tr>
                                    <td><span class="etichetta-codice"><?php echo htmlspecialchars($corso['codice_corso']); ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($corso['nome_corso']); ?></strong>
                                        <br>
                                        <small class="testo-disattivato"><?php echo substr($corso['descrizione'], 0, 50); ?>...</small>
                                    </td>
                                    <td><?php echo htmlspecialchars($corso['anno_accademico']); ?></td>
                                    <td><?php echo $corso['crediti']; ?></td>
                                    <td class="testo-destra">
                                        <a href="corsi.php?action=edit&id=<?php echo $corso['id']; ?>" class="btn btn- btn-contorno" title="Modifica">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="corsi.php" style="display:inline-block;" onsubmit="return confirm('ATTENZIONE: Eliminando il corso cancellerai anche tutti i compiti, materiali e voti associati! Sei sicuro?');">
                                            <input type="hidden" name="cmd" value="delete">
                                            <input type="hidden" name="corso_id" value="<?php echo $corso['id']; ?>">
                                            <button type="submit" class="btn btn- btn-pericolo" title="Elimina">
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
        <?php else: ?>
            <div class="nessun-contenuto">
                <i class="fas fa-folder-plus"></i>
                <p>Non hai ancora creato nessun corso.</p>
                <a href="corsi.php?action=create" class="btn btn-primario mt-2">Inizia ora</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include '../inclusi/footer.php'; ?>