<?php
require_once __DIR__ . '/Database.php';

class User {
    private $conn;
    private $nome_tabella = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();

    }

}


?>