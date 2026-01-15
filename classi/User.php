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
        $query = "UPDATE " . $this->nome_tabella . " 
            SET nome = :nome, cognome = :cognome, email = :email";

        if(!empty($dati['password'])) {
            $query .= ", password_hash = :password_hash";
        }

        $query .= " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $nome = htmlspecialchars(strip_tags($dati['nome']));
        $cognome = htmlspecialchars(strip_tags($dati['cognome']));
        $email = htmlspecialchars(strip_tags($dati['email']));

        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":cognome", $cognome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);

        if(!empty($dati['password'])) {
            $password_hash = password_hash($dati['password'], PASSWORD_BCRYPT);
            $stmt->bindParam(":password_hash", $password_hash);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    //restituisce la lista di tutti i studenti
    public function getAllStudents() {
        $query = "SELECT id, nome, cognome, email, matricola FROM " . $this->nome_tabella . " 
            WHERE ruolo = 'studente' ORDER BY cognome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $query = "DELETE FROM " . $this->nome_tabella . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }




}


?>