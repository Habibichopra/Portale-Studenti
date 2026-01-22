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
        $query = "UPDATE " . $this->nome_tabella . " SET 
                    nome_corso = :nome, 
                    codice_corso = :codice, 
                    descrizione = :descrizione, 
                    anno_accademico = :anno, 
                    crediti = :crediti 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        $nome = htmlspecialchars(strip_tags($dati['nome_corso']));
        $codice = htmlspecialchars(strip_tags($dati['codice_corso']));
        $descrizione = htmlspecialchars(strip_tags($dati['descrizione']));
        $anno = htmlspecialchars(strip_tags($dati['anno_accademico']));

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":codice", $codice);
        $stmt->bindParam(":descrizione", $descrizione);
        $stmt->bindParam(":anno", $anno);
        $stmt->bindParam(":crediti", $dati['crediti']);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //eliminazione corso
    public function eliminaCorso($id) {
        $query = "DELETE FROM " . $this->nome_tabella . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //ottieni corso con id
    public function getCorsoById($id) {
        $query = "SELECT c.*, u.nome as prof_nome, u.cognome as prof_cognome 
                  FROM " . $this->nome_tabella . " c
                  LEFT JOIN " . $this->tabella_users . " u ON c.professore_id = u.id
                  WHERE c.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //get di tutti i corsi
    public function getAllCorsi() {
        $query = "SELECT c.*, u.nome as prof_nome, u.cognome as prof_cognome 
                  FROM " . $this->nome_tabella . " c
                  LEFT JOIN " . $this->tabella_users . " u ON c.professore_id = u.id
                  ORDER BY c.nome_corso ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   

    //get corsi in base al professorei
    public function getCorsiByProfessore($professore_id) {
        $query = "SELECT * FROM " . $this->nome_tabella . " 
                  WHERE professore_id = ? 
                  ORDER BY nome_corso ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $professore_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //get corsi in base al studente
    public function getCorsiByStudente($studente_id) {
        $query = "SELECT c.*, i.id as iscrizione_id, i.data_iscrizione, 
                          u.nome as prof_nome, u.cognome as prof_cognome
                  FROM " . $this->nome_tabella . " c
                  JOIN " . $this->tabella_iscrizioni . " i ON c.id = i.corso_id
                  LEFT JOIN " . $this->tabella_users . " u ON c.professore_id = u.id
                  WHERE i.studente_id = ? AND i.status = 'attivo'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $studente_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //iscrizione studente al corso
    public function iscriviStudente($studente_id, $corso_id) {
    
    }

    //rimuovi iscrizione
    public function rimuoviIscrizione($iscrizione_id) {

    }

}

?>