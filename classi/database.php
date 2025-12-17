    <?php

    require_once __DIR__ . '/../config/database.php';

    class Database {
        //contiene l'unica istanza della classe Database
        private static $instance = null;
        
        //variabile che conterrà l'oggetto pdo
        private $conn;

        
        //Costruttore privato per evitare la creazione diretta dell'oggetto
        private function __construct() {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                
                //creazione connessione PDO
                $this->conn = new PDO($dsn, DB_USER, DB_PASS);
                
                //eccezioni in caso di errori SQL
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                //imposta il fetch mode di default su array associativo
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                //evita che pdo simula le query preparate lasciando il controllo al database
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                
            } catch (PDOException $e) {
                die("Errore di connessione al database: " . $e->getMessage());
            }
        }

        //metodo per ottenere l'istanza della classe che se non esiste la crea e se sesiste la ritorna
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new Database();
            }
            return self::$instance;
        }

        //restituisce oggetto pdo
        public function getConnection() {
            return $this->conn;
        }

        //chiusura connessione
        public function closeConnection() {
            $this->conn = null;
            self::$instance = null;
        }

        
        //prevenzione dalla clonazione del database
        private function __clone() {}

        
        //prevenzione dalla deserializzazione
        public function __wakeup() {}
    }
    ?>