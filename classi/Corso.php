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