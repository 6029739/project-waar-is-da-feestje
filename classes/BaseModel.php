<?php
/**
 * Abstract BaseModel Class
 * Basis class voor alle models met gemeenschappelijke functionaliteit
 * Abstract betekent: deze class kan niet direct gebruikt worden, alleen door overerving
 * 
 * Deze class implementeert de Validatable en Saveable interfaces.
 * Dat betekent dat alle child classes (die van BaseModel erven) automatisch ook deze interfaces implementeren.
 */
abstract class BaseModel implements Validatable, Saveable {
    public $id;
    public $db;
    
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
     * Deze methode komt van de Validatable interface
     */
    abstract public function validate($data);
    
    /**
     * Check of data geldig is (implementeert Validatable interface)
     * Gebruikt de validate() methode om te checken of er fouten zijn
     */
    public function isValid($data) {
        $errors = $this->validate($data);
        return empty($errors);
    }
    
    /**
     * Abstract methode voor save (implementeert Saveable interface)
     * Moet geïmplementeerd worden door child classes
     */
    abstract public function save($data);
    
    /**
     * Abstract methode voor update (implementeert Saveable interface)
     * Moet geïmplementeerd worden door child classes
     */
    abstract public function update($data);
    
    /**
     * Abstract methode voor delete (implementeert Saveable interface)
     * Moet geïmplementeerd worden door child classes
     */
    abstract public function delete();
    
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

