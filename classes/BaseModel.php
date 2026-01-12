<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Abstract BaseModel Class
 * Basis class voor alle models met gemeenschappelijke functionaliteit
 * Abstract betekent: deze class kan niet direct gebruikt worden, alleen door overerving
 */
abstract class BaseModel {
    protected $id;
    protected $db;
    
    /**
     * Constructor - wordt aangeroepen door child classes
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($id) {
            $this->loadFromDatabase($id);
        }
    }
    
    /**
     * Abstract methode - moet geïmplementeerd worden door child classes
     * Laad data uit de database
     */
    abstract protected function loadFromDatabase($id);
    
    /**
     * Abstract methode - moet geïmplementeerd worden door child classes
     * Valideer de data voordat het opgeslagen wordt
     */
    abstract protected function validate($data);
    
    /**
     * Haal ID op
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Check of het model bestaat (heeft een ID)
     */
    public function exists() {
        return $this->id !== null;
    }
    
    /**
     * Haal de database connectie op
     */
    protected function getDb() {
        return $this->db;
    }
}

