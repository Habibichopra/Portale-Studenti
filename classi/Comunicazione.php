<?php 
require_once __DIR__ . '/Database.php';

class Comunicazione {
    private $conn;
    private $nome_tabella = "comunicazioni";
    private $tabella_users = "users";
    private $tabella_corsi = "corsi";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

}

?>