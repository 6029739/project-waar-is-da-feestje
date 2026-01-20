<?php
/**
 * Script om een TEST admin account aan te maken met bekend wachtwoord
 * 
 * Dit maakt een admin account aan met:
 * Email: admin@test.nl
 * Wachtwoord: admin123
 * 
 * Gebruik dit alleen voor testen!
 */

require_once __DIR__ . '/config/autoload.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Test admin gegevens
    $adminEmail = 'admin@test.nl';
    $adminWachtwoord = 'admin123';
    $adminVoornaam = 'Admin';
    $adminAchternaam = 'Test';
    
    // Controleer of admin al bestaat
    $stmt = $db->prepare("SELECT id FROM gebruikers WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update bestaande gebruiker naar admin
        $hashedPassword = password_hash($adminWachtwoord, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE gebruikers SET wachtwoord = ?, rol = 'admin' WHERE email = ?");
        $stmt->execute([$hashedPassword, $adminEmail]);
        
        echo "✅ Bestaande gebruiker is nu een admin!\n\n";
    } else {
        // Maak nieuwe admin aan
        $hashedPassword = password_hash($adminWachtwoord, PASSWORD_DEFAULT);
        $stmt = $db->prepare(
            "INSERT INTO gebruikers (voornaam, achternaam, email, wachtwoord, rol) 
             VALUES (?, ?, ?, ?, 'admin')"
        );
        $stmt->execute([$adminVoornaam, $adminAchternaam, $adminEmail, $hashedPassword]);
        
        echo "✅ Admin account aangemaakt!\n\n";
    }
    
    echo "═══════════════════════════════════════════════════\n";
    echo "📧 ADMIN LOGIN GEGEVENS:\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "Email:    $adminEmail\n";
    echo "Wachtwoord: $adminWachtwoord\n";
    echo "═══════════════════════════════════════════════════\n\n";
    echo "⚠️  BELANGRIJK: Verwijder dit bestand na gebruik!\n";
    
} catch (Exception $e) {
    echo "❌ Fout: " . $e->getMessage() . "\n";
    echo "Controleer of de database correct is ingesteld.\n";
}
