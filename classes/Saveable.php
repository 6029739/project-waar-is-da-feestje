<?php
/**
 * Saveable Interface
 * 
 * Deze interface definieert methodes die classes moeten hebben om data op te slaan.
 * Classes die deze interface implementeren, kunnen data opslaan in de database.
 */
interface Saveable {
    /**
     * Sla data op in de database
     * 
     * @param array $data De data die opgeslagen moet worden
     * @return array Resultaat met success status en eventuele foutmeldingen
     */
    public function save($data);
    
    /**
     * Update bestaande data in de database
     * 
     * @param array $data De data die bijgewerkt moet worden
     * @return array Resultaat met success status en eventuele foutmeldingen
     */
    public function update($data);
    
    /**
     * Verwijder data uit de database
     * 
     * @return array Resultaat met success status
     */
    public function delete();
}
