<?php 
require_once __DIR__ . '/Database.php';

class Compito {
    private $conn;
    private $nome_tabella = "compiti";
    private $tabella_corsi = "corsi";
    private $tabella_iscrizioni = "iscrizioni";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
}
?>