<?php

define('SITE_NAME', 'Portale Studenti Universitario');
define('BASE_URL', 'http://localhost/Portale-Studenti/');

//avvio la sessione s enon è gia ccesa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>