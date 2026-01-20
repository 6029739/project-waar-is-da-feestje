-- Maak een admin account aan
-- Vervang 'admin@example.com' en 'jouwwachtwoord' door je eigen gegevens

USE waar_is_dat_feestje;

-- Optioneel: Verwijder bestaande admin als die al bestaat
-- DELETE FROM gebruikers WHERE email = 'admin@example.com';

-- Maak admin account aan
INSERT INTO gebruikers (voornaam, achternaam, email, wachtwoord, rol) 
VALUES (
    'Admin', 
    'Gebruiker', 
    'admin@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Dit is 'password' gehasht
    'admin'
);

-- Of gebruik dit commando om direct een wachtwoord te hashen:
-- Vervang 'jouwwachtwoord' door je gewenste wachtwoord
-- INSERT INTO gebruikers (voornaam, achternaam, email, wachtwoord, rol) 
-- VALUES ('Admin', 'Gebruiker', 'admin@example.com', PASSWORD('jouwwachtwoord'), 'admin');

-- Let op: PASSWORD() werkt alleen in MySQL 5.7 en ouder
-- Voor nieuwere versies, gebruik PHP password_hash() functie
