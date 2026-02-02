<?php

require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['ruolo'];
    switch ($role) {
        case 'studente':
            header("Location: studente/dashboard.php");
            exit;
        case 'professore':
            header("Location: professore/dashboard.php");
            exit;
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;
        default:
            header("Location: logout.php");
            exit;
    }
}

include 'inclusi/header.php';
include 'inclusi/nav.php';
?>

<header class="hero">
    <div class="container hero-content">
        <h1>Benvenuto nel tuo Portale Universitario</h1>
        <p>Gestisci i tuoi corsi, consegna i compiti e consulta i tuoi voti in un unico posto. Semplice, veloce e sempre accessibile.</p>
        <div class="hero-buttons">
            <a href="login.php" class="btn btn-primario">Accedi al Portale</a>
            <a href="register.php" class="btn btn-contorno">Crea un Account</a>
        </div>
    </div>
</header>

<section class="features">
    <div class="container">
        <h2>Cosa puoi fare?</h2>
        <div class="features-grid">
            
            <div class="feature-card">
                <div class="logo">📚</div>
                <h3>I tuoi Corsi</h3>
                <p>Accedi a tutto il materiale didattico, slide e videolezioni caricate dai tuoi professori.</p>
            </div>

            <div class="feature-card">
                <div class="logo">📝</div>
                <h3>Compiti e Esami</h3>
                <p>Visualizza le scadenze, invia i tuoi compiti online e ricevi feedback dettagliati.</p>
            </div>

            <div class="feature-card">
                <div class="logo">🏆</div>
                <h3>Libretto Online</h3>
                <p>Tieni traccia della tua media voti e scarica la tua pagella in qualsiasi momento.</p>
            </div>

        </div>
    </div>
</section>

<section class="info-section">
    <div class="container">
        <div class="info-text">
            <h2>Per Studenti e Docenti</h2>
            <p>Una piattaforma integrata che facilita la comunicazione e l'apprendimento. I docenti possono gestire le classi con facilità, mentre gli studenti hanno tutto ciò che serve per eccellere.</p>
        </div>
    </div>
</section>

<?php
include 'inclusi/footer.php';
?>