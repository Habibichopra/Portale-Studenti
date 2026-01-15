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
        $query = "SELECT id, username, password_hash, ruolo, nome, cognome FROM " . $this->nome_tabella . " WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        //se utente trovato prende i dati dell'utente
        if($stmt->rowCount() > 0) {
            $riga = $stmt->fetch(PDO::FETCH_ASSOC);
            //verificazione se la pasword inserita corrisponde all'hash
            if(password_verify($password, $riga['password_hash'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                //salvataggio dei dati nellla sessione
                $_SESSION['user_id'] = $riga['id'];
                $_SESSION['username'] = $riga['username'];
                $_SESSION['ruolo'] = $riga['ruolo'];
                $_SESSION['nome_completo'] = $riga['nome'] . " " . $riga['cognome'];
                $_SESSION['cognome'] = $riga['cognome']; 

                return true;
            }
        }
        return false;
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
        $query = "SELECT id, username, email, nome, cognome, ruolo, matricola, creato_il 
            FROM " . $this->nome_tabella . " WHERE id = ? LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
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