<?php
// Autoloading wordt geregeld door config/autoload.php

/**
 * Uitnodiging Class
 * Beheert uitnodigingen voor gasten
 */
class Uitnodiging {
    public $id;
    public $activiteit_id;
    public $email;
    public $token;
    public $status;
    public $uitgenodigd_door;
    public $db;
    
    public function __construct($id = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($id) {
            $this->loadFromDatabase($id);
        }
    }
    
    private function loadFromDatabase($id) {
        $stmt = $this->db->prepare("SELECT * FROM uitnodigingen WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->id = $data['id'];
            $this->activiteit_id = $data['activiteit_id'];
            $this->email = $data['email'];
            $this->token = $data['token'];
            $this->status = $data['status'];
            $this->uitgenodigd_door = $data['uitgenodigd_door'];
        }
    }
    
    /**
     * Nodig een gast uit
     */
    public function uitnodigen($activiteit_id, $email, $uitgenodigd_door) {
        // Genereer unieke token
        $token = bin2hex(random_bytes(32));
        
        $stmt = $this->db->prepare(
            "INSERT INTO uitnodigingen (activiteit_id, email, token, uitgenodigd_door) 
             VALUES (?, ?, ?, ?)"
        );
        
        if ($stmt->execute([$activiteit_id, $email, $token, $uitgenodigd_door])) {
            $this->id = $this->db->lastInsertId();
            $this->loadFromDatabase($this->id);
            
            // Stuur notificatie (als gebruiker al bestaat)
            $this->stuurUitnodigingNotificatie($email, $activiteit_id);
            
            return ['success' => true, 'token' => $token, 'id' => $this->id];
        }
        
        return ['success' => false, 'message' => 'Uitnodiging mislukt'];
    }
    
    /**
     * Accepteer uitnodiging via token
     */
    public static function accepteerViaToken($token, $gebruiker_id) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM uitnodigingen WHERE token = ? AND status = 'uitgenodigd'");
        $stmt->execute([$token]);
        $uitnodiging = $stmt->fetch();
        
        if (!$uitnodiging) {
            return ['success' => false, 'message' => 'Ongeldige of verlopen uitnodiging'];
        }
        
        // Update status
        $stmt = $db->prepare("UPDATE uitnodigingen SET status = 'geaccepteerd' WHERE id = ?");
        $stmt->execute([$uitnodiging['id']]);
        
        // Voeg toe als deelnemer
        $activiteit = new Activiteit($uitnodiging['activiteit_id']);
        $activiteit->aanmelden($gebruiker_id);
        
        return ['success' => true, 'message' => 'Uitnodiging geaccepteerd'];
    }
    
    /**
     * Stuur notificatie naar gebruiker (als die al bestaat)
     */
    private function stuurUitnodigingNotificatie($email, $activiteit_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM gebruikers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $stmt = $db->prepare(
                "INSERT INTO notificaties (gebruiker_id, activiteit_id, type, bericht) 
                 VALUES (?, ?, 'uitnodiging', ?)"
            );
            $stmt->execute([
                $user['id'], 
                $activiteit_id, 
                'Je bent uitgenodigd voor een activiteit'
            ]);
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getToken() { return $this->token; }
    public function getEmail() { return $this->email; }
}

