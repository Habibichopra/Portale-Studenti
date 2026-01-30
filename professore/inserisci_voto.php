<?php

require_once '../config/config.php';
$required_ruolo = 'professore';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';
require_once '../classi/Voto.php';
require_once '../classi/User.php';

$prof_id = $_SESSION['user_id'];
$corsoObj = new Corso();
$votoObj = new Voto();
$database = new Database();
$db = $database->getConnection();

$messaggio = '';
$errore = '';

$miei_corsi = $corsoObj->getCorsiByProfessore($prof_id);

$corso_selezionato_id = isset($_GET['corso_id']) ? $_GET['corso_id'] : '';
$studenti_iscritti = [];

if ($corso_selezionato_id) {

    $is_mio = false;
    foreach($miei_corsi as $c) { if($c['id'] == $corso_selezionato_id) $is_mio = true; }
    
    if($is_mio) {

        $query = "SELECT u.id, u.nome, u.cognome, u.matricola 
                  FROM users u 
                  JOIN iscrizioni i ON u.id = i.studente_id 
                  WHERE i.corso_id = :cid AND i.status = 'attivo' 
                  ORDER BY u.cognome ASC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":cid", $corso_selezionato_id);
        $stmt->execute();
        $studenti_iscritti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $corso_selezionato_id = ''; 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registra_voto'])) {
    
    $studente_id = $_POST['studente_id'];
    $tipo = $_POST['tipo_valutazione'];
    $voto = str_replace(',', '.', $_POST['voto']); 
    $note = trim($_POST['note']);
    $data_voto = $_POST['data_voto']; 

    if (empty($studente_id) || empty($voto)) {
        $errore = "Seleziona uno studente e inserisci un voto.";
    } elseif (!is_numeric($voto) || $voto < 0 || $voto > 31) { // 31 = 30L
        $errore = "Voto non valido (inserire un numero tra 0 e 30/31).";
    } else {
        // Inserimento
        if ($votoObj->addVoto($studente_id, $corso_selezionato_id, $tipo, $voto, $note)) {
            $messaggio = "Voto registrato con successo!";
        } else {
            $errore = "errore durante il salvataggio.";
        }
    }
}

define('PAGE_TITLE', 'Inserimento Voti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-edit"></i> Registra Voti</h1>
            <p>Inserisci manualmente voti per esami orali, scritti o progetti.</p>
        </div>
        <a href="dashboard.php" class="btn btn-contorno">&larr; Dashboard</a>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><i class="fas fa-check"></i> <?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><i class="fas fa-exclamation-circle"></i> <?php echo $errore; ?></div>
    <?php endif; ?>

    <div class="layout-diviso">
        
        <div>
            <div class="scheda">
                <div class="scheda-header">
                    <h2>Nuova Valutazione</h2>
                </div>
                <div class="body-scheda">
                    
                    <form method="GET" action="inserisci_voto.php" class="mb-4">
                        <div class="gruppo-form">
                            <label>Seleziona Corso:</label>
                            <select name="corso_id" class="controllo-form" onchange="this.form.submit()">
                                <option value="">-- Scegli materia --</option>
                                <?php foreach ($miei_corsi as $corso): ?>
                                    <option value="<?php echo $corso['id']; ?>" <?php echo ($corso_selezionato_id == $corso['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($corso['nome_corso']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if ($corso_selezionato_id): ?>
                        <hr class="separatore">
                        
                        <form method="POST" action="inserisci_voto.php?corso_id=<?php echo $corso_selezionato_id; ?>">
                            <input type="hidden" name="registra_voto" value="1">
                            
                            <div class="gruppo-form">
                                <label>Studente *</label>
                                <?php if (count($studenti_iscritti) > 0): ?>
                                    <select name="studente_id" class="controllo-form" required>
                                        <option value="">-- Seleziona Studente --</option>
                                        <?php foreach ($studenti_iscritti as $stud): ?>
                                            <option value="<?php echo $stud['id']; ?>">
                                                <?php echo htmlspecialchars($stud['cognome'] . ' ' . $stud['nome']); ?> 
                                                (Matr: <?php echo htmlspecialchars($stud['matricola']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <div class="alert alert-warning">Nessuno studente iscritto a questo corso.</div>
                                <?php endif; ?>
                            </div>

                            <div class="riga">
                                <div class="colonna-meta">
                                    <div class="gruppo-form">
                                        <label>Tipo Valutazione *</label>
                                        <select name="tipo_valutazione" class="controllo-form" required>
                                            <option value="esame">Esame</option>
                                            <option value="progetto">Progetto</option>
                                            <option value="compito">Compito in classe</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="colonna-meta">
                                    <div class="gruppo-form">
                                        <label>Voto (18-30) *</label>
                                        <input type="number" name="voto" class="controllo-form" step="0.5" min="0" max="31" required placeholder="Es: 28">
                                        <small class="testo-disattivato">Usa 31 per 30 e Lode</small>
                                    </div>
                                </div>
                            </div>

                            <div class="gruppo-form">
                                <label>Note (Opzionale)</label>
                                <textarea name="note" class="controllo-form" rows="3" placeholder="Es: Ottima esposizione orale..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primario btn-blocco" <?php echo empty($studenti_iscritti) ? 'disabled' : ''; ?>>
                                <i class="fas fa-save"></i> Registra Voto
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="testo-disattivato testo-centrato py-4">Seleziona un corso sopra per iniziare.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="action-column">
            <div class="scheda">
                <div class="scheda-header">
                    <h3>Ultimi Inserimenti</h3>
                </div>
                <div class="body-scheda">
                    <?php
                        $ultimi_voti = [];
                        if ($corso_selezionato_id) {
                             $query = "SELECT v.*, u.nome, u.cognome 
                                      FROM voti v 
                                      JOIN users u ON v.studente_id = u.id 
                                      WHERE v.corso_id = :cid 
                                      ORDER BY v.id DESC LIMIT 5";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(":cid", $corso_selezionato_id);
                            $stmt->execute();
                            $ultimi_voti = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }
                    ?>

                    <?php if ($corso_selezionato_id && count($ultimi_voti) > 0): ?>
                        <ul class="lista-attivita">
                            <?php foreach ($ultimi_voti as $v): ?>
                                <li class="oggetto-attivita">
                                    <div class="icona-attivita bg-successo">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="activity-info">
                                        <strong><?php echo htmlspecialchars($v['cognome'] . ' ' . $v['nome']); ?></strong>
                                        <br>
                                        <span>Voto: <strong><?php echo $v['voto']; ?></strong> (<?php echo ucfirst($v['tipo_valutazione']); ?>)</span>
                                        <br>
                                        <small class="testo-disattivato"><?php echo date('d/m/Y', strtotime($v['data_voto'])); ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif ($corso_selezionato_id): ?>
                        <p class="testo-disattivato text-">Nessun voto registrato per questo corso.</p>
                    <?php else: ?>
                        <p class="testo-disattivato text-">Seleziona un corso per vedere lo storico.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../inclusi/footer.php'; ?>