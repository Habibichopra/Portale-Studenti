<?php

require_once __DIR__ . '/Database.php';

class Materiale {
    private $conn;
    private $nome_tabella = "materiali";


    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    //caricamento nuovo materiale
    public function caricaMateriale($corso_id, $titolo, $descrizione, $tipo, $file) {

    }

    //eliminazione materiale
    public function eliminaMateriale($id) {

    }

    //ottenere tutti i materiali di un corso
    public function getMaterialiByCorso($corso_id) {

    }

    //download materiale
    public function downloadMateriale($id) {

    }
}

?>