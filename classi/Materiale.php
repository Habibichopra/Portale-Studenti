<?php

require_once __DIR__ . '/Database.php';

class Materiale {
    private $conn;
    private $nome_tabella = "materiali";


    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
}

?>