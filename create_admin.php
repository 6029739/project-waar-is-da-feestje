<?php
/**
 * Script om een admin account aan te maken
 * 
 * Gebruik:
 * 1. Pas de gegevens hieronder aan (email en wachtwoord)
 * 2. Open dit bestand in je browser of run via command line
 * 3. Verwijder dit bestand daarna voor veiligheid!
 */

require_once __DIR__ . '/config/autoload.php';

// ============================================
// PAS DEZE GEGEVENS AAN:
// ============================================
$adminEmail = 'admin@example.com';
$adminWachtwoord = 'admin123'; // Kies een sterk wachtwoord!
$adminVoornaam = 'Admin';
$adminAchternaam = 'Gebruiker';
// ============================================

try {
    $db = Database::getInstance()->getConnection();
    
    // Controleer of admin al bestaat
    $stmt = $db->prepare("SELECT id FROM gebruikers WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update bestaande gebruiker naar admin
        $hashedPassword = password_hash($adminWachtwoord, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE gebruikers SET wachtwoord = ?, rol = 'admin' WHERE email = ?");
        $stmt->execute([$hashedPassword, $adminEmail]);
        
        echo "✅ Bestaande gebruiker '$adminEmail' is nu een admin!\n";
        echo "Wachtwoord is bijgewerkt.\n";
    } else {
        // Maak nieuwe admin aan
        $hashedPassword = password_hash($adminWachtwoord, PASSWORD_DEFAULT);
        $stmt = $db->prepare(
            "INSERT INTO gebruikers (voornaam, achternaam, email, wachtwoord, rol) 
             VALUES (?, ?, ?, ?, 'admin')"
        );
        $stmt->execute([$adminVoornaam, $adminAchternaam, $adminEmail, $hashedPassword]);
        
        echo "✅ Admin account aangemaakt!\n";
        echo "Email: $adminEmail\n";
        echo "Wachtwoord: $adminWachtwoord\n";
    }
    
    echo "\n⚠️  BELANGRIJK: Verwijder dit bestand (create_admin.php) na gebruik voor veiligheid!\n";
    
} catch (Exception $e) {
    echo "❌ Fout: " . $e->getMessage() . "\n";
}
