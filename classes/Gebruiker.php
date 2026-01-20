<?php
/**
 * Gebruiker Class
 * Implementeert Validatable en Saveable interfaces via BaseModel
 */

/**
 * Gebruiker Class
 * Beheert alle functionaliteit rondom gebruikers
 * Erft van BaseModel (overerving)
 */
class Gebruiker extends BaseModel {
    public $voornaam;
    public $achternaam;
    public $email;
    public $rol;
    
    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    /**
     * Laad gebruiker gegevens uit de database
     * Implementeert abstracte methode van BaseModel
     */
    protected function loadFromDatabase($id) {
        $stmt = $this->db->prepare("SELECT * FROM gebruikers WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->id = $data['id'];
            $this->voornaam = $data['voornaam'];
            $this->achternaam = $data['achternaam'];
            $this->email = $data['email'];
            $this->rol = $data['rol'];
        }
    }
    
    /**
     * Registreer een nieuwe gebruiker
     */
    public static function registreer($voornaam, $achternaam, $email, $wachtwoord) {
        $db = Database::getInstance()->getConnection();
        
        // Validatie
        if (empty($voornaam) || empty($achternaam) || empty($email) || empty($wachtwoord)) {
            return ['success' => false, 'message' => 'Alle velden zijn verplicht'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Ongeldig emailadres'];
        }
        
        if (strlen($wachtwoord) < 6) {
            return ['success' => false, 'message' => 'Wachtwoord moet minimaal 6 tekens lang zijn'];
        }
        
        // Controleer of email al bestaat
        $stmt = $db->prepare("SELECT id FROM gebruikers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email is al in gebruik'];
        }
        
        // Hash het wachtwoord
        $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);
        
        // Voeg gebruiker toe
        try {
            $stmt = $db->prepare(
                "INSERT INTO gebruikers (voornaam, achternaam, email, wachtwoord) 
                 VALUES (?, ?, ?, ?)"
            );
            
            if ($stmt->execute([$voornaam, $achternaam, $email, $hashedPassword])) {
                return ['success' => true, 'message' => 'Registratie succesvol! Je kunt nu inloggen.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Er is een fout opgetreden bij de registratie'];
        }
        
        return ['success' => false, 'message' => 'Registratie mislukt'];
    }
    
    /**
     * Log in een gebruiker
     */
    public static function login($email, $wachtwoord) {
        $db = Database::getInstance()->getConnection();
        
        // Validatie
        if (empty($email) || empty($wachtwoord)) {
            return ['success' => false, 'message' => 'Email en wachtwoord zijn verplicht'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Ongeldig emailadres'];
        }
        
        $stmt = $db->prepare("SELECT * FROM gebruikers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Ongeldige email of wachtwoord'];
        }
        
        if (password_verify($wachtwoord, $user['wachtwoord'])) {
            // Start sessie
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_naam'] = $user['voornaam'] . ' ' . $user['achternaam'];
            $_SESSION['user_rol'] = $user['rol'];
            
            return ['success' => true, 'message' => 'Login succesvol! Welkom terug.'];
        }
        
        return ['success' => false, 'message' => 'Ongeldige email of wachtwoord'];
    }
    
    /**
     * Log uit de huidige gebruiker
     */
    public static function logout() {
        session_start();
        session_destroy();
        return ['success' => true, 'message' => 'Uitgelogd'];
    }
    
    /**
     * Haal alle gebruikers op (static methode)
     */
    public static function getAll() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, voornaam, achternaam, email, rol FROM gebruikers ORDER BY achternaam");
        return $stmt->fetchAll();
    }
    
    /**
     * Haal huidige gebruiker op uit sessie
     */
    public static function getCurrentUser() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            return new self($_SESSION['user_id']);
        }
        return null;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getVoornaam() { return $this->voornaam; }
    public function getAchternaam() { return $this->achternaam; }
    public function getEmail() { return $this->email; }
    public function getRol() { return $this->rol; }
    public function getVolledigeNaam() { 
        return $this->voornaam . ' ' . $this->achternaam; 
    }
    
    /**
     * Sla gebruiker op (implementeert Saveable interface)
     * Voor nieuwe gebruikers gebruik je de static registreer() methode
     */
    public function save($data) {
        return ['success' => false, 'message' => 'Gebruik Gebruiker::registreer() voor nieuwe gebruikers'];
    }
    
    /**
     * Update gebruiker (implementeert Saveable interface)
     */
    public function update($data) {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen gebruiker geselecteerd'];
        }
        
        // Valideer eerst
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        $stmt = $this->db->prepare(
            "UPDATE gebruikers SET 
             voornaam = ?, achternaam = ?, email = ?
             WHERE id = ?"
        );
        
        $result = $stmt->execute([
            $data['voornaam'] ?? $this->voornaam,
            $data['achternaam'] ?? $this->achternaam,
            $data['email'] ?? $this->email,
            $this->id
        ]);
        
        if ($result) {
            $this->loadFromDatabase($this->id);
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Bijwerken mislukt'];
    }
    
    /**
     * Verwijder gebruiker (implementeert Saveable interface)
     */
    public function delete() {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen gebruiker geselecteerd'];
        }
        
        $stmt = $this->db->prepare("DELETE FROM gebruikers WHERE id = ?");
        return ['success' => $stmt->execute([$this->id])];
    }
    
    /**
     * Valideer gebruiker data
     * Implementeert abstracte methode van BaseModel en Validatable interface
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['voornaam'])) {
            $errors[] = 'Voornaam is verplicht';
        }
        
        if (empty($data['achternaam'])) {
            $errors[] = 'Achternaam is verplicht';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geldig emailadres is verplicht';
        }
        
        if (empty($data['wachtwoord']) || strlen($data['wachtwoord']) < 6) {
            $errors[] = 'Wachtwoord moet minimaal 6 tekens lang zijn';
        }
        
        return $errors;
    }
}

