<?php 
require_once __DIR__ . '/Database.php';

class Consegna {
    private $conn;
    private $nome_tabella = "consegne";
    private $tabella_compiti = "compiti";
    private $tabella_users = "users";
    private $tabella_corsi = "corsi";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    //cosnega del compito da parte del stusente
    public function consegnaCompito($compito_id, $studente_id, $file, $note) {
        $cartellaDestinazione = __DIR__ . "/../importazioni/consegne/";
        
        if (!file_exists($cartellaDestinazione)) {
            mkdir($cartellaDestinazione, 0755, true);
        }

        $estensioneFile = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $nomeNuovoFile = $compito_id . "_" . $studente_id . "_" . time() . "." . $estensioneFile;
        $percorsoFileServer = $cartellaDestinazione . $nomeNuovoFile;
        $percorsoFileDatabase = "importazioni/consegne/" . $nomeNuovoFile;

        if (move_uploaded_file($file["tmp_name"], $percorsoFileServer)) {
            
            $query = "INSERT INTO " . $this->nome_tabella . " 
                      (compito_id, studente_id, file_consegna, note_studente, stato) 
                      VALUES (:compito_id, :studente_id, :file, :note, 'consegnato')";
            
            $stmt = $this->conn->prepare($query);

            $note = htmlspecialchars(strip_tags($note));

            $stmt->bindParam(":compito_id", $compito_id);
            $stmt->bindParam(":studente_id", $studente_id);
            $stmt->bindParam(":file", $percorsoFileDatabase);
            $stmt->bindParam(":note", $note);

            if ($stmt->execute()) {

                $ultimoID = $this->conn->lastInsertId();
                $this->checkRitardo($ultimoID); 
                return true;
            }
        }
        return false;
    }

    //valutazione del compito da parte del professore
    public function valutaConsegna($consegna_id, $voto, $feedback) {
        $query = "UPDATE " . $this->nome_tabella . " SET 
                    voto = :voto, 
                    feedback_professore = :feedback, 
                    stato = 'valutato' 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $feedback = htmlspecialchars(strip_tags($feedback));
        
        $stmt->bindParam(":voto", $voto);
        $stmt->bindParam(":feedback", $feedback);
        $stmt->bindParam(":id", $consegna_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //get consegna in base al id
    public function getConsegnaById($id) {
        $query = "SELECT c.*, u.nome, u.cognome, u.matricola, t.titolo as titolo_compito 
                  FROM " . $this->nome_tabella . " c
                  JOIN " . $this->tabella_users . " u ON c.studente_id = u.id
                  JOIN " . $this->tabella_compiti . " t ON c.compito_id = t.id
                  WHERE c.id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //lista delle consegne per un compito specifico
    public function getConsegneByCompito($compito_id) {
        $query = "SELECT c.*, u.nome, u.cognome, u.matricola 
                  FROM " . $this->nome_tabella . " c
                  JOIN " . $this->tabella_users . " u ON c.studente_id = u.id
                  WHERE c.compito_id = ? 
                  ORDER BY u.cognome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $compito_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //lista delle consegne per uno studente specifico
    public function getConsegneByStudente($studente_id) {
        $query = "SELECT c.*, t.titolo, t.data_scadenza, k.nome_corso 
                  FROM " . $this->nome_tabella . " c
                  JOIN " . $this->tabella_compiti . " t ON c.compito_id = t.id
                  JOIN " . $this->tabella_corsi . " k ON t.corso_id = k.id
                  WHERE c.studente_id = ? 
                  ORDER BY c.data_consegna DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $studente_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 

    //controllo se la consegna è in ritardo
    public function checkRitardo($consegna_id) {
        $query = "SELECT c.data_consegna, t.data_scadenza 
                  FROM " . $this->nome_tabella . " c
                  JOIN " . $this->tabella_compiti . " t ON c.compito_id = t.id
                  WHERE c.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $consegna_id);
        $stmt->execute();
        $riga = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($riga) {
            $data_consegna = new DateTime($riga['data_consegna']);
            $data_scadenza = new DateTime($riga['data_scadenza']);

            if ($data_consegna > $data_scadenza) {
                $update = "UPDATE " . $this->nome_tabella . " SET stato = 'in_ritardo' WHERE id = :id AND stato != 'valutato'";
                $upStmt = $this->conn->prepare($update);
                $upStmt->bindParam(":id", $consegna_id);
                $upStmt->execute();
                return true; 
            }
        }
        return false; 
    }

    //download del file della consegna
    public function downloadConsegna($id) {

    }
}

?>