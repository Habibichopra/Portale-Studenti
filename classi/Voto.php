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

    //inserimento voto
    public function addVoto($studente_id, $corso_id, $tipo, $voto, $note) {
        $query = "INSERT INTO " . $this->nome_tabella . " 
            (studente_id, corso_id, tipo_valutazione, voto, data_voto, note) 
            VALUES (:sid, :cid, :tipo, :voto, CURDATE(), :note)";
        
        $stmt = $this->conn->prepare($query);

        $note = htmlspecialchars(strip_tags($note));
        $tipo = htmlspecialchars(strip_tags($tipo));

        $stmt->bindParam(":sid", $studente_id);
        $stmt->bindParam(":cid", $corso_id);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":voto", $voto);
        $stmt->bindParam(":note", $note);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //aggiorna voto
    public function aggiornaVoto($id, $voto, $note) {
        $query = "UPDATE " . $this->nome_tabella . " 
                  SET voto = :voto, note = :note 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $note = htmlspecialchars(strip_tags($note));

        $stmt->bindParam(":voto", $voto);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //eliminazione del voto
    public function eliminaVoto($id) {
        $query = "DELETE FROM " . $this->nome_tabella . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //get voti di un studente
    public function getVotiByStudente($studente_id, $corso_id = null) {
        $query = "SELECT v.*, c.nome_corso, c.codice_corso 
                  FROM " . $this->nome_tabella . " v
                  JOIN " . $this->tabella_corsi . " c ON v.corso_id = c.id
                  WHERE v.studente_id = :sid";
        
        if ($corso_id) {
            $query .= " AND v.corso_id = :cid";
        }
        
        $query .= " ORDER BY v.data_voto DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sid", $studente_id);
        
        if ($corso_id) {
            $stmt->bindParam(":cid", $corso_id);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //calcola media voti studente
    public function calcolaMedia($studente_id, $corso_id = null) {
        $query = "SELECT AVG(voto) as media FROM " . $this->nome_tabella . " WHERE studente_id = :sid";

        if ($corso_id) {
            $query .= " AND corso_id = :cid";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sid", $studente_id);
        
        if ($corso_id) {
            $stmt->bindParam(":cid", $corso_id);
        }

        $stmt->execute();
        $riga = $stmt->fetch(PDO::FETCH_ASSOC);

        return $riga['media'] ? round($riga['media'], 2) : 0;
    }

    //statistiche del corso
    public function getStatisticheCorso($corso_id) {
        $query = "SELECT 
            AVG(voto) as media_corso, 
            MAX(voto) as voto_max, 
            MIN(voto) as voto_min, 
            COUNT(*) as totale_voti 
            FROM " . $this->nome_tabella . " 
            WHERE corso_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $corso_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //generazione pagella in formato CSV
    public function generatePagella($studente_id) {
        $voti = $this->getVotiByStudente($studente_id);
        
        if (empty($voti)) {
            return false;
        }


        $filename = "pagella_" . $studente_id . "_" . time() . ".csv";
        $export_dir = __DIR__ . "/../esportazioni/";
        
        if (!file_exists($export_dir)) {
            mkdir($export_dir, 0777, true);
        }
        
        $filepath = $export_dir . $filename;

        $file = fopen($filepath, 'w');

        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($file, array('Corso', 'Codice', 'Tipo Valutazione', 'Voto', 'Data', 'Note'));

        foreach ($voti as $voto) {
            fputcsv($file, array(
                $voto['nome_corso'],
                $voto['codice_corso'],
                ucfirst($voto['tipo_valutazione']),
                $voto['voto'],
                $voto['data_voto'],
                $voto['note']
            ));
        }

        fclose($file);

        return $filename;
    }
}

?>