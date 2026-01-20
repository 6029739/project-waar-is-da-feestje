<?php
/**
 * Validatable Interface
 * 
 * Een interface is een contract dat classes moeten volgen.
 * Het definieert welke methodes een class MOET hebben, maar zegt niet HOE ze geïmplementeerd moeten worden.
 * 
 * Waarom gebruiken we een interface?
 * - Het zorgt ervoor dat alle classes die deze interface implementeren, dezelfde methodes hebben
 * - Dit maakt de code voorspelbaarder en makkelijker te onderhouden
 * - Je kunt verschillende classes op dezelfde manier gebruiken (polymorfisme)
 */
interface Validatable {
    /**
     * Valideer data
     * Elke class die deze interface implementeert, MOET deze methode hebben
     * 
     * @param array $data De data die gevalideerd moet worden
     * @return array Array met foutmeldingen (leeg als alles goed is)
     */
    public function validate($data);
    
    /**
     * Check of de data geldig is
     * 
     * @param array $data De data die gecontroleerd moet worden
     * @return bool True als geldig, false als ongeldig
     */
    public function isValid($data);
}
