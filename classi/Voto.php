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

    }

    //generazione pagella in formato CSV
    public function generatePagella($studente_id) {
    
    }
}

?>