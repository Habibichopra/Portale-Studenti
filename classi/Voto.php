<?php 

require_once __DIR__ . '/Database.php';
class Voto{
    private $conn;
    private $nome_tabella = "voti";
    private $tabella_corsi = "corsi";
    private $tabella_users = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
}

?>