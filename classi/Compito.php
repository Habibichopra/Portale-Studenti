<?php 
require_once __DIR__ . '/Database.php';

class Compito {
    private $conn;
    private $nome_tabella = "compiti";
    private $tabella_corsi = "corsi";
    private $tabella_iscrizioni = "iscrizioni";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }
    
    //creazione di un nuovo compito
    public function creaCompito($corso_id, $titolo, $descrizione, $scadenza, $punti_max, $allegato = null) {
    
    }

    //aggorna compito
    public function aggiornaCompito($id, $dati) {
    
    }

    //elimina un compito
    public function eliminaCompito($id) {
    
    }

    //compito in base al id
    public function getCompitoById($id) {
    
    }

    //compiti in base al corso
    public function getCompitiByCorso($corso_id) {

    }

    //trovare compiti scaduti
    public function getCompitiScaduti($corso_id = null) {

    }

    //compiti in scadenza nei prossimi giorni
    public function getCompitiProssimi($giorni = 7, $studente_id = null) {
    
    }
}
?>