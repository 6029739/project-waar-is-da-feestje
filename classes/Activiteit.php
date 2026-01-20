<?php
/**
 * Activiteit Class
 * Implementeert Validatable en Saveable interfaces via BaseModel
 */

/**
 * Activiteit Class
 * Beheert alle functionaliteit rondom activiteiten
 * Erft van BaseModel (overerving)
 */
class Activiteit extends BaseModel {
    public $titel;
    public $beschrijving;
    public $datum;
    public $tijd;
    public $locatie;
    public $soort;
    public $status;
    public $opmerkingen;
    public $organisator_id;
    
    public function __construct($id = null) {
        parent::__construct($id);
    }
    
    /**
     * Laad activiteit gegevens uit de database
     * Implementeert abstracte methode van BaseModel
     */
    protected function loadFromDatabase($id) {
        $stmt = $this->db->prepare("SELECT * FROM activiteiten WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->id = $data['id'];
            $this->titel = $data['titel'];
            $this->beschrijving = $data['beschrijving'];
            $this->datum = $data['datum'];
            $this->tijd = $data['tijd'];
            $this->locatie = $data['locatie'];
            $this->soort = $data['soort'];
            $this->status = $data['status'];
            $this->opmerkingen = $data['opmerkingen'];
            $this->organisator_id = $data['organisator_id'];
        }
    }
    
    /**
     * Maak een nieuwe activiteit aan
     * Implementeert de save() methode van de Saveable interface
     */
    public function save($data) {
        return $this->aanmaken($data);
    }
    
    /**
     * Maak een nieuwe activiteit aan
     */
    public function aanmaken($data) {
        // Valideer eerst
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO activiteiten 
             (titel, beschrijving, datum, tijd, locatie, soort, status, opmerkingen, organisator_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            $data['titel'],
            $data['beschrijving'] ?? null,
            $data['datum'],
            $data['tijd'],
            $data['locatie'],
            $data['soort'],
            $data['status'] ?? 'gepland',
            $data['opmerkingen'] ?? null,
            $data['organisator_id']
        ]);
        
        if ($result) {
            $this->id = $this->db->lastInsertId();
            $this->loadFromDatabase($this->id);
            
            // Stuur notificatie naar alle gebruikers
            $this->stuurNotificatie('nieuwe_activiteit', 'Er is een nieuwe activiteit aangemaakt: ' . $this->titel);
            
            return ['success' => true, 'id' => $this->id];
        }
        
        return ['success' => false, 'message' => 'Activiteit aanmaken mislukt'];
    }
    
    /**
     * Werk een activiteit bij
     * Implementeert de update() methode van de Saveable interface
     */
    public function update($data) {
        return $this->bijwerken($data);
    }
    
    /**
     * Werk een activiteit bij
     */
    public function bijwerken($data) {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen activiteit geselecteerd'];
        }
        
        $stmt = $this->db->prepare(
            "UPDATE activiteiten SET 
             titel = ?, beschrijving = ?, datum = ?, tijd = ?, locatie = ?, 
             soort = ?, status = ?, opmerkingen = ?
             WHERE id = ?"
        );
        
        $result = $stmt->execute([
            $data['titel'] ?? $this->titel,
            $data['beschrijving'] ?? $this->beschrijving,
            $data['datum'] ?? $this->datum,
            $data['tijd'] ?? $this->tijd,
            $data['locatie'] ?? $this->locatie,
            $data['soort'] ?? $this->soort,
            $data['status'] ?? $this->status,
            $data['opmerkingen'] ?? $this->opmerkingen,
            $this->id
        ]);
        
        if ($result) {
            $this->loadFromDatabase($this->id);
            
            // Stuur notificatie naar deelnemers
            $this->stuurNotificatie('wijziging', 'Activiteit "' . $this->titel . '" is gewijzigd');
            
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Bijwerken mislukt'];
    }
    
    /**
     * Verwijder een activiteit
     * Implementeert de delete() methode van de Saveable interface
     */
    public function delete() {
        return $this->verwijderen();
    }
    
    /**
     * Verwijder een activiteit
     */
    public function verwijderen() {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen activiteit geselecteerd'];
        }
        
        // Stuur eerst notificatie
        $this->stuurNotificatie('annulering', 'Activiteit "' . $this->titel . '" is geannuleerd');
        
        $stmt = $this->db->prepare("DELETE FROM activiteiten WHERE id = ?");
        return ['success' => $stmt->execute([$this->id])];
    }
    
    /**
     * Schrijf gebruiker in voor activiteit
     */
    public function aanmelden($gebruiker_id) {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen activiteit geselecteerd'];
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO deelnemers (activiteit_id, gebruiker_id) VALUES (?, ?)"
        );
        
        try {
            $stmt->execute([$this->id, $gebruiker_id]);
            return ['success' => true, 'message' => 'Aangemeld voor activiteit'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Je bent al aangemeld voor deze activiteit'];
        }
    }
    
    /**
     * Meld gebruiker af voor activiteit
     */
    public function afmelden($gebruiker_id) {
        if (!$this->id) {
            return ['success' => false, 'message' => 'Geen activiteit geselecteerd'];
        }
        
        $stmt = $this->db->prepare(
            "DELETE FROM deelnemers WHERE activiteit_id = ? AND gebruiker_id = ?"
        );
        
        return ['success' => $stmt->execute([$this->id, $gebruiker_id])];
    }
    
    /**
     * Haal alle deelnemers op
     */
    public function getDeelnemers() {
        if (!$this->id) {
            return [];
        }
        
        $stmt = $this->db->prepare(
            "SELECT g.id, g.voornaam, g.achternaam, g.email 
             FROM deelnemers d
             JOIN gebruikers g ON d.gebruiker_id = g.id
             WHERE d.activiteit_id = ?"
        );
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Stuur notificatie naar alle deelnemers
     */
    private function stuurNotificatie($type, $bericht) {
        if (!$this->id) return;
        
        $deelnemers = $this->getDeelnemers();
        $stmt = $this->db->prepare(
            "INSERT INTO notificaties (gebruiker_id, activiteit_id, type, bericht) 
             VALUES (?, ?, ?, ?)"
        );
        
        foreach ($deelnemers as $deelnemer) {
            $stmt->execute([$deelnemer['id'], $this->id, $type, $bericht]);
        }
    }
    
    /**
     * Haal alle activiteiten op
     */
    public static function getAll($filters = []) {
        $db = Database::getInstance()->getConnection();
        $query = "SELECT a.*, g.voornaam, g.achternaam 
                  FROM activiteiten a
                  JOIN gebruikers g ON a.organisator_id = g.id
                  WHERE 1=1";
        $params = [];
        
        if (!empty($filters['soort'])) {
            $query .= " AND a.soort = ?";
            $params[] = $filters['soort'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['zoekterm'])) {
            $query .= " AND (a.titel LIKE ? OR a.beschrijving LIKE ? OR a.locatie LIKE ?)";
            $searchTerm = '%' . $filters['zoekterm'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['datum_van'])) {
            $query .= " AND a.datum >= ?";
            $params[] = $filters['datum_van'];
        }
        
        if (!empty($filters['datum_tot'])) {
            $query .= " AND a.datum <= ?";
            $params[] = $filters['datum_tot'];
        }
        
        $query .= " ORDER BY a.datum ASC, a.tijd ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Haal activiteiten op voor kalender (per maand)
     */
    public static function getVoorKalender($jaar, $maand) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT a.*, g.voornaam, g.achternaam 
             FROM activiteiten a
             JOIN gebruikers g ON a.organisator_id = g.id
             WHERE YEAR(a.datum) = ? AND MONTH(a.datum) = ?
             ORDER BY a.datum ASC, a.tijd ASC"
        );
        $stmt->execute([$jaar, $maand]);
        return $stmt->fetchAll();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getTitel() { return $this->titel; }
    public function getBeschrijving() { return $this->beschrijving; }
    public function getDatum() { return $this->datum; }
    public function getTijd() { return $this->tijd; }
    public function getLocatie() { return $this->locatie; }
    public function getSoort() { return $this->soort; }
    public function getStatus() { return $this->status; }
    public function getOpmerkingen() { return $this->opmerkingen; }
    public function getOrganisatorId() { return $this->organisator_id; }
    
    /**
     * Valideer activiteit data
     * Implementeert abstracte methode van BaseModel en Validatable interface
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['titel'])) {
            $errors[] = 'Titel is verplicht';
        }
        
        if (empty($data['datum'])) {
            $errors[] = 'Datum is verplicht';
        } else {
            $datum = new DateTime($data['datum']);
            $nu = new DateTime();
            if ($datum < $nu) {
                $errors[] = 'Datum mag niet in het verleden liggen';
            }
        }
        
        if (empty($data['tijd'])) {
            $errors[] = 'Tijd is verplicht';
        }
        
        if (empty($data['locatie'])) {
            $errors[] = 'Locatie is verplicht';
        }
        
        if (empty($data['soort']) || !in_array($data['soort'], ['binnen', 'buiten'])) {
            $errors[] = 'Soort moet binnen of buiten zijn';
        }
        
        if (empty($data['organisator_id'])) {
            $errors[] = 'Organisator is verplicht';
        }
        
        return $errors;
    }
}

