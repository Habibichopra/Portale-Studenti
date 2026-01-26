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
        $percorsoFile = $this->export_dir . $nomeFile;
        $file = fopen($percorsoFile, 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        return $file;
    }

    //Genera CSV con i voti di uno studente specifico
    public function exportVotiStudente($studente_id) {
        $query = "SELECT c.nome_corso, c.codice_corso, v.tipo_valutazione, v.voto, v.data_voto, v.note 
                  FROM voti v
                  JOIN corsi c ON v.corso_id = c.id
                  WHERE v.studente_id = ?
                  ORDER BY v.data_voto DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $studente_id);
        $stmt->execute();
        
        $nomeFile = "voti_studente_" . $studente_id . "_" . time() . ".csv";
        $file = $this->apriFileCSV($nomeFile);

        fputcsv($file, array('Corso', 'Codice', 'Tipo', 'Voto', 'Data', 'Note'));

        while ($riga = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($file, $riga);
        }

        fclose($file);
        return $nomeFile;
    }

    //Metodo per esportare le presenze di un corso specifico in un file CSV
    public function exportPresenze($corso_id) {

    }

    //genera statistcihe corso con csv
    public function exportStatisticheCorso($corso_id) {

    }

    //importa studenti da un file csv
    public function importaStudentiDaCSV($file_input) {

    }


}

?>