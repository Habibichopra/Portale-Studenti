<?php
require_once '../config/config.php';

$required_ruolo = 'studente';
require_once '../inclusi/session_check.php';

require_once '../classi/Corso.php';

$corsoObj = new Corso();
$studente_id = $_SESSION['user_id'];

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['azione']) && $_POST['azione'] === 'disiscriviti') {
        $iscrizione_id = $_POST['iscrizione_id'];
        if ($corsoObj->rimuoviIscrizione($iscrizione_id)) {
            $messaggio = "Ti sei disiscritto dal corso con successo.";
        } else {
            $errore = "errore durante la disiscrizione.";
        }
    }

    if (isset($_POST['azione']) && $_POST['azione'] === 'iscriviti') {
        $corso_id = $_POST['corso_id'];
        if ($corsoObj->iscriviStudente($studente_id, $corso_id)) {
            $messaggio = "Iscrizione effettuata! Ora puoi vedere i materiali.";
        } else {
            $errore = "Impossibile iscriversi. Forse sei già iscritto?";
        }
    }
}

$miei_corsi = $corsoObj->getCorsiByStudente($studente_id);
$tutti_corsi = $corsoObj->getAllCorsi();

$ids_miei_corsi = array_column($miei_corsi, 'id');
$corsi_disponibili = [];

foreach ($tutti_corsi as $corso) {
    if (!in_array($corso['id'], $ids_miei_corsi)) {
        $corsi_disponibili[] = $corso;
    }
}

define('PAGE_TITLE', 'I Miei Corsi');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">

    <header class="header-pagina">
        <h1><i class="fas fa-book-open"></i> I Miei Corsi</h1>
        <p>Gestisci le tue iscrizioni e accedi ai materiali didattici.</p>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><?php echo $errore; ?></div>
    <?php endif; ?>

    <section class="mb-5">
        <h2>Corsi Attivi</h2>
        
        <?php if (count($miei_corsi) > 0): ?>
            <div class="griglia-corsi">
                <?php foreach ($miei_corsi as $corso): ?>
                    <div class="scheda-corso">
                        <div class="header-corso">
                            <span class="etichetta-codice"><?php echo htmlspecialchars($corso['codice_corso']); ?></span>
                            <span class="etichetta-crediti"><?php echo $corso['crediti']; ?> CFU</span>
                        </div>
                        
                        <div class="body-corso">
                            <h3><?php echo htmlspecialchars($corso['nome_corso']); ?></h3>
                            <p>
                                <i class="fas fa-chalkboard-teacher"></i> 
                                Prof. <?php echo htmlspecialchars($corso['prof_nome'] . ' ' . $corso['prof_cognome']); ?>
                            </p>
                            <p>
                                <?php 
                                echo substr(htmlspecialchars($corso['descrizione']), 0, 100) . '...'; 
                                ?>
                            </p>
                        </div>

                        <div class="footer-corso">
                            <a href="materiali.php?corso_id=<?php echo $corso['id']; ?>" class="btn btn-primario btn-sm">
                                <i class="fas fa-folder-open"></i> Materiali
                            </a>

                            <form method="POST" action="corsi.php" onsubmit="return confirm('Sei sicuro di voler abbandonare questo corso? Perderai l\'accesso ai compiti.');">
                                <input type="hidden" name="azione" value="disiscriviti">
                                <input type="hidden" name="iscrizione_id" value="<?php echo $corso['iscrizione_id']; ?>">
                                <button type="submit" class="btn btn-pericolo btn-sm btn-icona">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="nessun-contenuto">
                <i class="fas fa-graduation-cap"></i>
                <p>Non sei iscritto a nessun corso al momento.</p>
            </div>
        <?php endif; ?>
    </section>

    <hr class="separatore">

    <section>
        <h2>Catalogo Corsi Disponibili</h2>
        <p class="testo-disattivato mb-3">Iscriviti a nuovi corsi per visualizzare materiali e compiti.</p>

        <?php if (count($corsi_disponibili) > 0): ?>
            <div class="tabella-responsive">
                <table class="tabella-semplice tabella-hover">
                    <thead>
                        <tr>
                            <th>Codice</th>
                            <th>Corso</th>
                            <th>Professore</th>
                            <th>Anno</th>
                            <th>Crediti</th>
                            <th>Azione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($corsi_disponibili as $corso): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($corso['codice_corso']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($corso['nome_corso']); ?>
                                    <br>
                                    <small class="testo-disattivato"><?php echo substr($corso['descrizione'], 0, 50); ?>...</small>
                                </td>
                                <td><?php echo htmlspecialchars($corso['prof_nome'] . ' ' . $corso['prof_cognome']); ?></td>
                                <td><?php echo htmlspecialchars($corso['anno_accademico']); ?></td>
                                <td><?php echo $corso['crediti']; ?></td>
                                <td>
                                    <form method="POST" action="corsi.php">
                                        <input type="hidden" name="azione" value="iscriviti">
                                        <input type="hidden" name="corso_id" value="<?php echo $corso['id']; ?>">
                                        <button type="submit" class="btn btn-contorno btn-sm">
                                            <i class="fas fa-plus"></i> Iscriviti
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="testo-centrato">Non ci sono altri corsi disponibili a cui iscriversi.</p>
        <?php endif; ?>
    </section>

</div>


<?php include '../inclusi/footer.php'; ?>  