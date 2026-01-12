<?php 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

if (isset($required_ruolo) && $_SESSION['ruolo'] !== $required_ruolo) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>