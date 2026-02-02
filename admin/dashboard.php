<?php

require_once '../config/config.php';

$required_ruolo = 'admin';
require_once '../inclusi/session_check.php';

require_once '../classi/User.php';
require_once '../classi/Corso.php';

$userObj = new User();
$corsoObj = new Corso();

$studenti = $userObj->getAllStudents();
$professori = $userObj->getAllProfessori();
$corsi = $corsoObj->getAllCorsi();

$tot_studenti = count($studenti);
$tot_professori = count($professori);
$tot_corsi = count($corsi);

define('PAGE_TITLE', 'Pannello Amministratore');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container dashboard-container">
    
    <header class="dashboard-header flex-header">
        <div>
            <h1><i class="fas fa-user-shield"></i> Pannello di Controllo</h1>
            <p>Benvenuto Amministratore. Gestisci utenti, corsi e configurazioni.</p>
        </div>
        <div>
            <span class="badge-ruolo admin-badge">Admin</span>
        </div>
    </header>

    <div class="griglia-statistiche">
        <div class="scheda-statistiche">
            <div class="icona-statistiche"><i class="fas fa-user-graduate"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_studenti; ?></h3>
                <p>Studenti Registrati</p>
            </div>
        </div>

        <div class="scheda-statistiche verde">
            <div class="icona-statistiche"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_professori; ?></h3>
                <p>Professori Attivi</p>
            </div>
        </div>

        <div class="scheda-statistiche viola">
            <div class="icona-statistiche"><i class="fas fa-book"></i></div>
            <div class="info-statistiche">
                <h3><?php echo $tot_corsi; ?></h3>
                <p>Corsi Erogati</p>
            </div>
        </div>
    </div>

    <hr class="separatore">

    <div class="dashboard-main-griglia">
        
        <section class="scheda">
            <div class="scheda-header">
                <h2><i class="fas fa-rocket"></i> Azioni Rapide</h2>
            </div>
            <div class="body-scheda">
                <div class="scheda-azione-admin">
                    
                    <a href="gestione_utenti.php" class="scheda-azione">
                        <div class="icona"><i class="fas fa-users-cog"></i></div>
                        <h3>Gestione Utenti</h3>
                        <p>Aggiungi, modifica o elimina studenti e docenti.</p>
                    </a>

                    <a href="gestione_corsi.php" class="scheda-azione">
                        <div class="icona"><i class="fas fa-layer-group"></i></div>
                        <h3>Gestione Corsi</h3>
                        <p>Crea nuovi insegnamenti e assegna cattedre.</p>
                    </a>

                    <a href="import_csv.php" class="scheda-azione evidenzia">
                        <div class="icona"><i class="fas fa-file-import"></i></div>
                        <h3>Importa Studenti</h3>
                        <p>Caricamento massivo da file CSV.</p>
                    </a>

                    <a href="#" class="scheda-azione" onclick="alert('Funzionalità di backup globale in arrivo!'); return false;">
                        <div class="icona"><i class="fas fa-database"></i></div>
                        <h3>Backup Dati</h3>
                        <p>Esporta l'intero database.</p>
                    </a>

                </div>
            </div>
        </section>

        <section class="scheda">
            <div class="scheda-header">
                <h2><i class="fas fa-history"></i> Ultimi Iscritti</h2>
                <a href="gestione_utenti.php" class="btn-testo">Vedi tutti &rarr;</a>
            </div>
            <div class="body-scheda">
                <div class="tabella-responsive">
                    <table class="tabella-semplice table-sm">
                        <thead>
                            <tr>
                                <th>Utente</th>
                                <th>Ruolo</th>
                                <th>Matricola</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recenti = array_merge($studenti, $professori);
                            usort($recenti, function($a, $b) {
                                return $b['id'] - $a['id'];
                            });
                            $recenti = array_slice($recenti, 0, 5);
                            ?>

                            <?php foreach ($recenti as $u): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['cognome'] . ' ' . $u['nome']); ?></strong>
                                        <br><small class="testo-disattivato">@<?php echo htmlspecialchars($u['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php if(isset($u['matricola']) && $u['matricola']): ?>
                                            <span class="badge-ruolo studente">Studente</span>
                                        <?php else: ?>
                                            <span class="badge-ruolo prof">Professore</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo isset($u['matricola']) ? $u['matricola'] : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>
</div>

<?php include '../inclusi/footer.php'; ?>