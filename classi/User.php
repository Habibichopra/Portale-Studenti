<?php
require_once __DIR__ . '/Database.php';

class User {
    private $conn;
    private $nome_tabella = "users";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();

    }

    //registra un nuovo utente nel database
    public function registra($username, $password, $email, $nome, $cognome, $ruolo, $matricola = null) {
        
    }

    //effettua il login dell utente
    public function login($username, $password) {
    
    }
    
    //effettua il logout dell'tente
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset(); 
        session_destroy();
        return true;
    }

    //restituisce i dati dell'utente in base
    public function getUserById($id) {

    }

    //aggiorna il profilo delutente
    public function updateProfile($id, $dati){

    }

    //restituisce la lista di tutti i studenti
    public function getAllStudents() {

    }

    //restituisce la lista di tutti i professori
    public function getAllProfessori() {
        $query = "SELECT id, nome, cognome, email FROM " . $this->nome_tabella . " 
                  WHERE ruolo = 'professore' ORDER BY cognome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    //elimino utente in base al id
    public function deleteUser($id) {

    }




}


?>