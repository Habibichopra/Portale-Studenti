<?php

require_once '../config/config.php';
$required_ruolo = 'admin';
require_once '../inclusi/session_check.php';
require_once '../classi/User.php';

$userObj = new User();

$database = new Database();
$db = $database->getConnection();

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['azione']) && $_POST['azione'] === 'elimina') {
        $id_to_delete = $_POST['user_id'];
        
        if ($id_to_delete == $_SESSION['user_id']) {
            $errore = "Non puoi eliminare il tuo stesso account mentre sei loggato.";
        } else {
            if ($userObj->deleteUser($id_to_delete)) {
                $messaggio = "Utente eliminato con successo.";
            } else {
                $errore = "errore durante l'eliminazione.";
            }
        }
    }


    elseif (isset($_POST['azione']) && $_POST['azione'] === 'crea') {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $ruolo = $_POST['ruolo'];
        $matricola = ($ruolo === 'studente') ? trim($_POST['matricola']) : null;

        if (empty($nome) || empty($username) || empty($password)) {
            $errore = "Compila tutti i campi obbligatori.";
        } else {
            if ($userObj->register($username, $password, $email, $nome, $cognome, $ruolo, $matricola)) {
                $messaggio = "Nuovo utente creato correttamente.";
            } else {
                $errore = "errore: Username o Email già esistenti.";
            }
        }
    }
    elseif (isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
        $id = $_POST['user_id'];
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $email = trim($_POST['email']);
        $ruolo = $_POST['ruolo'];
        $matricola = ($ruolo === 'studente') ? trim($_POST['matricola']) : null;
        $password = $_POST['password']; 

        $query = "UPDATE users SET nome=:nome, cognome=:cognome, email=:email, ruolo=:ruolo, matricola=:matricola";

        if (!empty($password)) {
            $query .= ", password_hash=:pwd";
        }
        $query .= " WHERE id=:id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":cognome", $cognome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":ruolo", $ruolo);
        $stmt->bindParam(":matricola", $matricola);
        $stmt->bindParam(":id", $id);

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bindParam(":pwd", $hash);
        }

        try {
            if ($stmt->execute()) {
                $messaggio = "Dati utente aggiornati.";
            } else {
                $errore = "errore nell'aggiornamento.";
            }
        } catch (PDOException $e) {
            $errore = "errore SQL: Probabile duplicato di email.";
        }
    }
}


$view = isset($_GET['view']) ? $_GET['view'] : 'lista';
$edit_user = null;

if ($view === 'modifica' && isset($_GET['id'])) {
    $edit_user = $userObj->getUserById($_GET['id']);
}


$query_all = "SELECT * FROM users ORDER BY ruolo ASC, cognome ASC";
$stmt = $db->prepare($query_all);
$stmt->execute();
$lista_utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

define('PAGE_TITLE', 'Gestione Utenti');
include '../inclusi/header.php';
include '../inclusi/nav.php';
?>

<div class="container layout-contenuto">
    
    <header class="header-pagina flex-header">
        <div>
            <h1><i class="fas fa-users-cog"></i> Gestione Utenti</h1>
            <p>Amministrazione completa account studenti e docenti.</p>
        </div>
        <?php if ($view === 'lista'): ?>
            <a href="gestione_utenti.php?view=crea" class="btn btn-primario">
                <i class="fas fa-user-plus"></i> Nuovo Utente
            </a>
        <?php else: ?>
            <a href="gestione_utenti.php" class="btn btn-contorno">
                &larr; Torna alla Lista
            </a>
        <?php endif; ?>
    </header>

    <?php if ($messaggio): ?>
        <div class="alert alert-successo"><i class="fas fa-check"></i> <?php echo $messaggio; ?></div>
    <?php endif; ?>
    <?php if ($errore): ?>
        <div class="alert alert-errore"><i class="fas fa-exclamation-circle"></i> <?php echo $errore; ?></div>
    <?php endif; ?>

    <?php if ($view === 'crea' || $view === 'modifica'): ?>
        <div class="scheda">
            <div class="scheda-header">
                <h2><?php echo ($view === 'modifica') ? 'Modifica Utente' : 'Crea Nuovo Utente'; ?></h2>
            </div>
            <div class="body-scheda">
                <form method="POST" action="gestione_utenti.php">
                    <input type="hidden" name="azione" value="<?php echo $view; ?>">
                    <?php if ($edit_user): ?>
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    <?php endif; ?>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Nome *</label>
                                <input type="text" name="nome" class="controllo-form" required 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['nome']) : ''; ?>">
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Cognome *</label>
                                <input type="text" name="cognome" class="controllo-form" required 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['cognome']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Email *</label>
                                <input type="email" name="email" class="controllo-form" required 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>">
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Username <?php echo $view == 'modifica' ? '(Non modificabile)' : '*'; ?></label>
                                <input type="text" name="username" class="controllo-form" 
                                       <?php echo $view == 'modifica' ? 'disabled' : 'required'; ?> 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="riga">
                        <div class="colonna-meta">
                            <div class="gruppo-form">
                                <label>Ruolo *</label>
                                <select name="ruolo" id="ruoloSelect" class="controllo-form" onchange="toggleMatricola()">
                                    <option value="studente" <?php echo ($edit_user && $edit_user['ruolo'] == 'studente') ? 'selected' : ''; ?>>Studente</option>
                                    <option value="professore" <?php echo ($edit_user && $edit_user['ruolo'] == 'professore') ? 'selected' : ''; ?>>Professore</option>
                                    <option value="admin" <?php echo ($edit_user && $edit_user['ruolo'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="colonna-meta">
                            <div class="gruppo-form" id="matricolaGroup">
                                <label>Matricola (Solo Studenti)</label>
                                <input type="text" name="matricola" class="controllo-form" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['matricola']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="gruppo-form">
                        <label>Password <?php echo $view == 'modifica' ? '(Lascia vuoto per non cambiare)' : '*'; ?></label>
                        <input type="password" name="password" class="controllo-form" 
                               <?php echo $view == 'crea' ? 'required' : ''; ?>>
                    </div>

                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primario">
                            <i class="fas fa-save"></i> Salva Utente
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function toggleMatricola() {
            var ruolo = document.getElementById('ruoloSelect').value;
            var group = document.getElementById('matricolaGroup');
            if (ruolo === 'studente') {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        }
        // Esegui al caricamento per settare lo stato corretto in modifica
        document.addEventListener('DOMContentLoaded', toggleMatricola);
        </script>

    <?php else: ?>
        
        <div class="scheda">
            <div class="tabella-responsive">
                <table class="tabella-semplice tabella-hover">
                    <thead>
                        <tr>
                            <th>Ruolo</th>
                            <th>Utente</th>
                            <th>Email</th>
                            <th>Matricola</th>
                            <th class="testo-destra">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_utenti as $u): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $badgeClass = 'badge-grigio';
                                    if ($u['ruolo'] == 'admin') $badgeClass = 'badge-scuro';
                                    if ($u['ruolo'] == 'professore') $badgeClass = 'badge-sucesso';
                                    if ($u['ruolo'] == 'studente') $badgeClass = 'badge-primario';
                                    ?>
                                    <span class="badge-ruolo <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($u['ruolo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['cognome'] . ' ' . $u['nome']); ?></strong>
                                    <br><small class="testo-disattivato">@<?php echo htmlspecialchars($u['username']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo $u['matricola'] ? htmlspecialchars($u['matricola']) : '-'; ?></td>
                                <td class="testo-destra">
                                    <a href="gestione_utenti.php?view=modifica&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-contorno" title="Modifica">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="gestione_utenti.php" style="display:inline;" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?');">
                                            <input type="hidden" name="azione" value="elimina">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-pericolo" title="Elimina">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<style>

.badge-ruolo { padding: 4px 8px; border-radius: 4px; color: white; font-size: 0.8em; }
.badge-scuro { background-color: #34495e; }
.badge-sucesso { background-color: #27ae60; }
.badge-primario { background-color: #3498db; }
</style>

<?php include '../inclusi/footer.php'; ?>