-- Database voor "Waar is dat feestje?" applicatie
-- Maak eerst de database aan: CREATE DATABASE waar_is_dat_feestje;

USE waar_is_dat_feestje;

-- Tabel voor gebruikers
CREATE TABLE IF NOT EXISTS gebruikers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voornaam VARCHAR(100) NOT NULL,
    achternaam VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    wachtwoord VARCHAR(255) NOT NULL,
    rol ENUM('gebruiker', 'admin') DEFAULT 'gebruiker',
    aangemaakt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel voor activiteiten
CREATE TABLE IF NOT EXISTS activiteiten (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titel VARCHAR(255) NOT NULL,
    beschrijving TEXT,
    datum DATE NOT NULL,
    tijd TIME NOT NULL,
    locatie VARCHAR(255) NOT NULL,
    soort ENUM('binnen', 'buiten') NOT NULL,
    status ENUM('gepland', 'geannuleerd', 'voltooid') DEFAULT 'gepland',
    opmerkingen TEXT,
    organisator_id INT NOT NULL,
    aangemaakt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    bijgewerkt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organisator_id) REFERENCES gebruikers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel voor deelnemers (veel-op-veel relatie tussen gebruikers en activiteiten)
CREATE TABLE IF NOT EXISTS deelnemers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activiteit_id INT NOT NULL,
    gebruiker_id INT NOT NULL,
    aangemeld_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activiteit_id) REFERENCES activiteiten(id) ON DELETE CASCADE,
    FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deelnemer (activiteit_id, gebruiker_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel voor uitnodigingen (voor gasten die nog niet geregistreerd zijn)
CREATE TABLE IF NOT EXISTS uitnodigingen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activiteit_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('uitgenodigd', 'geaccepteerd', 'geweigerd') DEFAULT 'uitgenodigd',
    uitgenodigd_door INT NOT NULL,
    aangemaakt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activiteit_id) REFERENCES activiteiten(id) ON DELETE CASCADE,
    FOREIGN KEY (uitgenodigd_door) REFERENCES gebruikers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel voor notificaties
CREATE TABLE IF NOT EXISTS notificaties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gebruiker_id INT NOT NULL,
    activiteit_id INT,
    type ENUM('wijziging', 'uitnodiging', 'annulering', 'nieuwe_activiteit') NOT NULL,
    bericht TEXT NOT NULL,
    gelezen BOOLEAN DEFAULT FALSE,
    aangemaakt_op TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gebruiker_id) REFERENCES gebruikers(id) ON DELETE CASCADE,
    FOREIGN KEY (activiteit_id) REFERENCES activiteiten(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexen voor betere performance
CREATE INDEX idx_activiteiten_datum ON activiteiten(datum);
CREATE INDEX idx_activiteiten_organisator ON activiteiten(organisator_id);
CREATE INDEX idx_deelnemers_activiteit ON deelnemers(activiteit_id);
CREATE INDEX idx_deelnemers_gebruiker ON deelnemers(gebruiker_id);
CREATE INDEX idx_notificaties_gebruiker ON notificaties(gebruiker_id);
CREATE INDEX idx_notificaties_gelezen ON notificaties(gelezen);

