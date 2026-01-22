<?php 
require_once __DIR__ . '/Database.php';

class Corso {
    private $conn;
    private $nome_tabella = "corsi";
    private $tabella_iscrizioni = "iscrizioni";
    private $tabella_users = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    //creazione di un corso
    public function createCorso($nome, $codice, $descrizione, $anno, $professore_id, $crediti) {
        $query = "INSERT INTO " . $this->nome_tabella . " 
                  (nome_corso, codice_corso, descrizione, anno_accademico, professore_id, crediti) 
                  VALUES (:nome, :codice, :descrizione, :anno, :prof_id, :crediti)";
        
        $stmt = $this->conn->prepare($query);

        $nome = htmlspecialchars(strip_tags($nome));
        $codice = htmlspecialchars(strip_tags($codice));
        $descrizione = htmlspecialchars(strip_tags($descrizione));
        $anno = htmlspecialchars(strip_tags($anno));

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":codice", $codice);
        $stmt->bindParam(":descrizione", $descrizione);
        $stmt->bindParam(":anno", $anno);
        $stmt->bindParam(":prof_id", $professore_id);
        $stmt->bindParam(":crediti", $crediti);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false; 
            }
            throw $e;
        }
        return false;
    }

    //aggiorna corso
    public function aggiornaCorso($id, $dati) {
    
    }

    //eliminazione corso
    public function eliminaCorso($id) {

    }

    //ottieni corso con id
    public function getCorsoById($id) {
    
    }

    //get di tutti i corsi
    public function getAllCorsi() {
    
    }   

    //get corsi in base al professorei
    public function getCorsiByProfessore($professore_id) {

    }

    //get corsi in base al studente
    public function getCorsiByStudente($studente_id) {

    }

    //iscrizione studente al corso
    public function iscriviStudente($studente_id, $corso_id) {
    
    }

    //rimuovi iscrizione
    public function rimuoviIscrizione($iscrizione_id) {

    }

}

?>