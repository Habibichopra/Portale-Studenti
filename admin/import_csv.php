<?php
require_once '../config/config.php';
$required_ruolo = 'admin';
require_once '../inclusi/session_check.php';
require_once '../classi/EsportatoreCSV.php';

$csvExporter = new EsportatoreCSV();


if (isset($_GET['azione']) && $_GET['azione'] === 'download_template') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="template_import_studenti.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, array('username', 'password', 'email', 'nome', 'cognome', 'matricola'));
    fputcsv($output, array('mario.rossi', 'P@ssword123', 'm.rossi@studenti.it', 'Mario', 'Rossi', 'MAT1001'));
    fputcsv($output, array('luca.bianchi', 'P@ssword123', 'l.bianchi@studenti.it', 'Luca', 'Bianchi', 'MAT1002'));
    
    fclose($output);
    exit; 
}

$risultato = null;
$errore_file = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['errore'] === 0) {
        
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        
        if ($ext === 'csv') {
            $risultato = $csvExporter->importStudentiDaCSV($_FILES['csv_file']);
        } else {
            $errore_file = "Formato non valido. Carica un file .csv";
        }

    } else {
        $errore_file = "Seleziona un file valido prima di inviare.";
    }
}

define('PAGE_TITLE', 'Importazione Massiva');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina">
        <h1><i class="fas fa-file-import"></i> Importazione Studenti</h1>
        <p>Carica massivamente gli account studenti da un file CSV.</p>
    </header>

    <div class="layout-diviso">
        
        <div>
            <div class="scheda">
                <div class="scheda-header">
                    <h3>Istruzioni</h3>
                </div>
                <div class="body-scheda">
                    <p>Per importare correttamente gli studenti, il file CSV deve rispettare questo formato esatto:</p>
                    
                    <div class="code-block">
                        username,password,email,nome,cognome,matricola
                    </div>
                    
                    <ul class="info-lista mt-3">
                        <li>I campi devono essere separati da virgola (<code>,</code>).</li>
                        <li>La password verrà cifrata automaticamente.</li>
                        <li>L'email e l'username devono essere univoci.</li>
                    </ul>

                    <hr class="separatore">
                    
                    <a href="import_csv.php?action=download_template" class="btn btn-contorno btn-blocco">
                        <i class="fas fa-download"></i> Scarica Template CSV
                    </a>
                </div>
            </div>
        </div>

        <div class="action-column">
            <div class="scheda">
                <div class="scheda-header">
                    <h3>Carica File</h3>
                </div>
                <div class="body-scheda">
                    
                    <?php if ($errore_file): ?>
                        <div class="alert alert-errore"><?php echo $errore_file; ?></div>
                    <?php endif; ?>

                    <?php if ($risultato): ?>
                        <?php if ($risultato['successo']): ?>
                            <div class="alert alert-successo">
                                <i class="fas fa-check-circle"></i> Operazione completata!<br>
                                <strong><?php echo $risultato['importato']; ?></strong> studenti inseriti con successo.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($risultato['errori'])): ?>
                            <div class="alert alert-errore">
                                <strong>Attenzione:</strong> Alcune righe non sono state importate:
                                <ul class="errore-lista mt-2">
                                    <?php foreach ($risultato['errori'] as $err): ?>
                                        <li><?php echo htmlspecialchars($err); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form method="POST" action="import_csv.php" enctype="multipart/form-data" class="upload-area">
                        <div class="gruppo-form testo-centrato">
                            <i class="fas fa-file-csv fa-3x testo-disattivato mb-3"></i>
                            <label for="csv_file" class="d-block">Trascina il file qui o clicca per selezionare</label>
                            <input type="file" name="csv_file" id="csv_file" class="controllo-form-file" accept=".csv" required>
                        </div>

                        <button type="submit" class="btn btn-primario btn-large btn-blocco mt-3">
                            <i class="fas fa-cloud-upload-alt"></i> Avvia Importazione
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<style>
.code-block {
    background: #f4f6f7;
    padding: 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9em;
    border: 1px solid #ddd;
    overflow-x: auto;
}
.info-lista { padding-left: 20px; font-size: 0.9em; color: #555; }
.info-lista li { margin-bottom: 5px; }

.upload-area {
    border: 2px dashed #bdc3c7;
    padding: 30px;
    border-radius: 8px;
    background: #fdfdfd;
    transition: all 0.3s;
}
.upload-area:hover {
    border-color: #3498db;
    background: #f0f8ff;
}
.errore-lista { 
    padding-left: 15px; 
    font-size: 0.85em; 
    max-height: 150px; 
    overflow-y: auto; 
}
</style>

<?php include '../inclusi/footer.php'; ?>