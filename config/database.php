<?php
/**
 * Database Class - Singleton Pattern
 * Deze class zorgt voor de database connectie
 * Singleton betekent: er kan maar één database connectie zijn
 */
class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuratie
    private $host = 'localhost';
    private $dbname = 'waar_is_dat_feestje';
    private $username = 'root';
    private $password = '';
    
    /**
     * Private constructor - voorkomt dat je direct een nieuw object maakt
     */
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Gooi exception in plaats van die() zodat API het kan afhandelen
            throw new Exception("Database connectie mislukt: " . $e->getMessage());
        }
    }
    
    /**
     * Static methode om de enige instantie te krijgen
     * Als er nog geen instantie is, wordt er een nieuwe gemaakt
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Haal de database connectie op
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Voorkomt dat het object gekloond wordt
     */
    private function __clone() {}
    
    /**
     * Voorkomt dat het object geserialiseerd wordt
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

