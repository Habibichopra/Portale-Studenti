<?php 
require_once __DIR__ . '/Database.php';

class Corso {
    private $conn;
    private $nome_tabella = "corsi";
    private $tabella_iscrizioni = "iscrizioni";
    private $tabella_users = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }









}

?>