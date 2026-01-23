<?php 
require_once __DIR__ . '/Database.php';

class Comunicazione {
    private $conn;
    private $nome_tabella = "comunicazioni";
    private $tabella_users = "users";
    private $tabella_corsi = "corsi";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }


    public function inviaComunicazione($mittente_id, $destinatario_id, $corso_id, $oggetto, $messaggio) {
        $query = "INSERT INTO " . $this->nome_tabella . " 
                  (mittente_id, destinatario_id, corso_id, oggetto, messaggio, data_invio, letto) 
                  VALUES (:mittente, :destinatario, :corso, :oggetto, :messaggio, NOW(), 0)";
        
        $stmt = $this->conn->prepare($query);

        $oggetto = htmlspecialchars(strip_tags($oggetto));
        $messaggio = htmlspecialchars(strip_tags($messaggio));

        $stmt->bindParam(":mittente", $mittente_id);
        
        if (empty($destinatario_id)) {
            $destinatario_id = null;
        }
        $stmt->bindParam(":destinatario", $destinatario_id);

        if (empty($corso_id)) {
            $corso_id = null;
        }
        $stmt->bindParam(":corso", $corso_id);

        $stmt->bindParam(":oggetto", $oggetto);
        $stmt->bindParam(":messaggio", $messaggio);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    //recuparare tutte le comunicazioni per un utente specifico
    public function getComunicazioniByUser($user_id) {

    }

    //sergnare una comunicazione come letta
    public function segnaComeLetto($comunicazione_id) {

    }

    //eliminare una comunicazione
    public function eliminaComunicazione($id) {
    
    }
}

?>