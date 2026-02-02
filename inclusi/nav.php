<?php
require_once __DIR__ . '/../config/config.php'; 
?>

<nav class="navbar">

    <div class="container nav-container">
        <a href="<?php echo BASE_URL; ?>index.php" class="logo">
            <i class="fas fa-graduation-cap"></i> Portale Studenti
        </a>

        <ul class="nav-links" id="navLinks">
            
            <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                
                <?php if ($_SESSION['ruolo'] == 'studente'): ?>
                    <li><a href="<?php echo BASE_URL; ?>studente/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>studente/corsi.php">Miei Corsi</a></li>
                    <li><a href="<?php echo BASE_URL; ?>studente/voti.php">Libretto</a></li>
                    <li><a href="<?php echo BASE_URL; ?>studente/comunicazioni.php">Messaggi</a></li>
                
                <?php elseif ($_SESSION['ruolo'] == 'professore'): ?>
                    <li><a href="<?php echo BASE_URL; ?>professore/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>professore/corsi.php">Gestione Corsi</a></li>
                    <li><a href="<?php echo BASE_URL; ?>professore/studenti.php">Studenti</a></li>
                    
                <?php elseif ($_SESSION['ruolo'] == 'admin'): ?>
                    <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/gestione_utenti.php">Utenti</a></li>
                    <li><a href="<?php echo BASE_URL; ?>admin/gestione_corsi.php">Corsi</a></li>
                <?php endif; ?>

                <li class="user-menu">
                    <span>
                        <i class="fas fa-user-circle"></i> 
                        <?php echo htmlspecialchars($_SESSION['nome_completo'] ?? 'Utente'); ?>
                    </span>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Esci
                    </a>
                </li>

            <?php else: ?>
                
                <li><a href="<?php echo BASE_URL; ?>login.php">Accedi</a></li>
                <li><a href="<?php echo BASE_URL; ?>register.php" class="btn-register">Registrati</a></li>
            
            <?php endif; ?>
        </ul>

        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>