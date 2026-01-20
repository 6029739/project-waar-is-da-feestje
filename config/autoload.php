<?php
/**
 * Autoloading Configuratie
 * 
 * Autoloading betekent dat PHP automatisch classes laadt wanneer je ze nodig hebt.
 * Je hoeft niet meer handmatig require_once of include te gebruiken!
 * 
 * Hoe werkt het?
 * 1. Wanneer je een class gebruikt (bijv. new Activiteit())
 * 2. Zoekt PHP eerst of de class al geladen is
 * 3. Als niet, roept PHP de autoload functie aan
 * 4. De autoload functie zoekt het bestand en laadt het automatisch
 * 
 * Voordelen:
 * - Geen require_once meer nodig
 * - Minder code
 * - Minder kans op fouten
 * - Betere performance (laadt alleen wat nodig is)
 */

/**
 * Autoload functie
 * Deze functie wordt automatisch aangeroepen door PHP wanneer een class niet gevonden wordt
 * 
 * @param string $className De naam van de class die geladen moet worden
 */
spl_autoload_register(function ($className) {
    // Basis directory waar alle classes staan
    $baseDir = __DIR__ . '/../classes/';
    
    // Maak het bestandspad: classes/ClassName.php
    $file = $baseDir . $className . '.php';
    
    // Als het bestand bestaat, laad het
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    
    // Als de class Database is, laad die uit config
    if ($className === 'Database') {
        $file = __DIR__ . '/database.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Als de class niet gevonden wordt, gooi een error
    // (In productie zou je dit misschien anders willen afhandelen)
    throw new Exception("Class '$className' niet gevonden in $file");
});

// Laad de Database class altijd eerst (die heeft geen namespace)
require_once __DIR__ . '/database.php';
