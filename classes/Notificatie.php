<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Notificatie Class
 * Beheert notificaties voor gebruikers
 */
class Notificatie {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Haal alle notificaties op voor een gebruiker
     */
    public function getVoorGebruiker($gebruiker_id, $alleen_ongelezen = false) {
        $query = "SELECT * FROM notificaties WHERE gebruiker_id = ?";
        if ($alleen_ongelezen) {
            $query .= " AND gelezen = 0";
        }
        $query .= " ORDER BY aangemaakt_op DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$gebruiker_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Markeer notificatie als gelezen
     */
    public function markeerAlsGelezen($notificatie_id, $gebruiker_id) {
        $stmt = $this->db->prepare(
            "UPDATE notificaties SET gelezen = 1 WHERE id = ? AND gebruiker_id = ?"
        );
        return $stmt->execute([$notificatie_id, $gebruiker_id]);
    }
    
    /**
     * Markeer alle notificaties als gelezen
     */
    public function markeerAllesAlsGelezen($gebruiker_id) {
        $stmt = $this->db->prepare(
            "UPDATE notificaties SET gelezen = 1 WHERE gebruiker_id = ?"
        );
        return $stmt->execute([$gebruiker_id]);
    }
    
    /**
     * Tel ongelezen notificaties
     */
    public function telOngelezen($gebruiker_id) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as aantal FROM notificaties WHERE gebruiker_id = ? AND gelezen = 0"
        );
        $stmt->execute([$gebruiker_id]);
        $result = $stmt->fetch();
        return $result['aantal'] ?? 0;
    }
}

