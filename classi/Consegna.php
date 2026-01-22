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

    }

    //get consegna in base al id
    public function getConsegnaById($id) {

    }

    //lista delle consegne per un compito specifico
    public function getConsegneByCompito($compito_id) {

    }

    //lista delle consegne per uno studente specifico
    public function getConsegneByStudente($studente_id) {

    } 

    //controllo se la consegna è in ritardo
    public function checkRitardo($consegna_id) {

    }

    //download del file della consegna
    public function downloadConsegna($id) {

    }
}

?>