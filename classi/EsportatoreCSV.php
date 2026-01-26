<?php
require_once __DIR__ . '/Database.php';

class EsportatoreCSV  {
    private $conn;
    private $export_dir;

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
}

?>