<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/User.php'; 
require_once '../classi/CSVExporter.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$csvExporter = new EsportatoreCSV();

$database = Database::getInstance();
$db = $database->getConnection();

$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);
$corso_selezionato_id = isset($_GET['corso_id']) ? $_GET['corso_id'] : '';

$download_link = '';
if (isset($_POST['export_presenze']) && $corso_selezionato_id) {
    $filename = $csvExporter->exportPresenze($corso_selezionato_id);
    if ($filename) {
        $download_link = BASE_URL . 'esportazioni/' . $filename;
    }
}


$lista_studenti = [];
if ($corso_selezionato_id) {
    $is_mio = false;
    foreach($miei_corsi as $c) { if($c['id'] == $corso_selezionato_id) $is_mio = true; }

    if ($is_mio) {
        $query = "SELECT u.id, u.nome, u.cognome, u.email, u.matricola, i.data_iscrizione, i.status 
                  FROM users u
                  JOIN iscrizioni i ON u.id = i.studente_id
                  WHERE i.corso_id = :cid
                  ORDER BY u.cognome ASC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(":cid", $corso_selezionato_id);
        $stmt->execute();
        $lista_studenti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $corso_selezionato_id = ''; 
    }
}

define('PAGE_TITLE', 'Lista Studenti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-users"></i> Gestione Classe</h1>
            <p>Visualizza gli iscritti e gestisci le presenze.</p>
        </div>
        
        <?php if ($corso_selezionato_id): ?>
            <form method="POST">
                <button type="submit" name="export_presenze" class="btn btn-contorno">
                    <i class="fas fa-file-csv"></i> Scarica Registro Presenze
                </button>
            </form>
        <?php endif; ?>
    </header>

    <?php if ($download_link): ?>
        <div class="alert alert-successo">
            <i class="fas fa-check"></i> Registro generato! 
            <a href="<?php echo $download_link; ?>" class="alert-link" download>Scarica il file Excel/CSV qui.</a>
        </div>
    <?php endif; ?>

    <div class="scheda mb-4">
        <div class="body-scheda">
            <form method="GET">
                <label><strong>Visualizza classe di:</strong></label>
                <select name="corso_id" class="controllo-form" onchange="this.form.submit()">
                    <option value="">-- Seleziona un corso --</option>
                    <?php foreach ($miei_corsi as $corso): ?>
                        <option value="<?php echo $corso['id']; ?>" <?php echo ($corso_selezionato_id == $corso['id']) ? 'selected' : ''; ?>>
                            [<?php echo htmlspecialchars($corso['codice_corso']); ?>] 
                            <?php echo htmlspecialchars($corso['nome_corso']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($corso_selezionato_id): ?>
        <div class="scheda">
            <div class="scheda-header">
                <h3>Elenco Iscritti (<?php echo count($lista_studenti); ?>)</h3>
            </div>
            
            <div class="body-scheda">
                <?php if (count($lista_studenti) > 0): ?>
                    <div class="tabella-responsive">
                        <table class="tabella-semplice tabella-hover">
                            <thead>
                                <tr>
                                    <th>Matricola</th>
                                    <th>Studente</th>
                                    <th>Email</th>
                                    <th>Data Iscrizione</th>
                                    <th>Stato</th>
                                    <th class="testo-destra">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_studenti as $studente): ?>
                                    <tr>
                                        <td>
                                            <span class="etichetta-codice"><?php echo htmlspecialchars($studente['matricola']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($studente['cognome'] . ' ' . $studente['nome']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($studente['email']); ?>" class="testo-disattivato">
                                                <?php echo htmlspecialchars($studente['email']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($studente['data_iscrizione'])); ?></td>
                                        <td>
                                            <?php if($studente['status'] == 'attivo'): ?>
                                                <span class="stato-punto verde"></span> Attivo
                                            <?php else: ?>
                                                <span class="stato-punto rosso"></span> Ritirato
                                            <?php endif; ?>
                                        </td>
                                        <td class="testo-destra">
                                            <a href="comunicazioni.php" class="btn btn-sm btn-primario" title="Invia Messaggio">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            
                                            </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="nessun-contenuto">
                        <i class="fas fa-user-graduate"></i>
                        <p>Nessuno studente risulta iscritto a questo corso.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <?php else: ?>
        <div class="nessun-contenuto">
            <i class="fas fa-chalkboard-teacher"></i>
            <p>Seleziona un corso sopra per visualizzare la lista degli studenti.</p>
        </div>
    <?php endif; ?>

</div>


<?php include '../inclusi/footer.php'; ?>