<?php
require_once __DIR__ . '/Database.php';

class EsportatoreCSV  {
    private $conn;
    private $export_dir;

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();

         $this->export_dir = __DIR__ . "/../esportazioni/";
        
        if (!file_exists($this->export_dir)) {
            mkdir($this->export_dir, 0755, true);
        }
    }

    //metodo per aprire un file csv
    private function apriFileCSV($nomeFile) {

    }

    //Genera CSV con i voti di uno studente specifico
    public function exportVotiStudente($studente_id) {

    }

    //Metodo per esportare le presenze di un corso specifico in un file CSV
    public function exportPresenze($corso_id) {

    }

    //genera statistcihe corso con csv
    public function exportStatisticheCorso($corso_id) {

    }


}

?>