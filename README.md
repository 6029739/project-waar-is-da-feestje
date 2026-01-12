# Waar is dat feestje? - Activiteiten Planner

Een webapplicatie voor het plannen, beheren en communiceren rondom activiteiten voor teams.

## Functionaliteiten

- ✅ Gebruikersregistratie en inloggen
- ✅ Activiteiten aanmaken, bewerken en verwijderen
- ✅ Onderscheid tussen binnen- en buitenactiviteiten
- ✅ Weersinformatie voor buitenactiviteiten (via OpenWeatherMap API)
- ✅ Kalenderweergave van activiteiten
- ✅ Zoek- en filterfunctionaliteit
- ✅ Gasten uitnodigen voor activiteiten
- ✅ Notificatiesysteem voor wijzigingen
- ✅ Aanmelden/afmelden voor activiteiten
- ✅ Objectgeoriënteerde PHP en JavaScript

## Technologieën

- **Backend**: PHP 7.4+ met OOP
- **Frontend**: HTML5, CSS3, JavaScript (ES6 modules)
- **Database**: MySQL/MariaDB
- **API**: RESTful API endpoints
- **Weer API**: OpenWeatherMap (vereist API key)

## Installatie

### 1. Database Setup

1. Maak een nieuwe database aan in MySQL:
```sql
CREATE DATABASE waar_is_dat_feestje;
```

2. Importeer de database structuur:
```bash
mysql -u root -p waar_is_dat_feestje < database.sql
```

Of gebruik phpMyAdmin om het `database.sql` bestand te importeren.

### 2. Database Configuratie

Pas de database instellingen aan in `config/database.php`:
- `$host`: Database host (standaard: 'localhost')
- `$dbname`: Database naam (standaard: 'waar_is_dat_feestje')
- `$username`: Database gebruikersnaam (standaard: 'root')
- `$password`: Database wachtwoord (standaard: '')

### 3. Weer API Setup

1. Registreer een gratis account op [OpenWeatherMap](https://openweathermap.org/api)
2. Haal je API key op
3. Pas `api/weer.php` aan en vervang `'YOUR_API_KEY'` met je eigen API key

### 4. Webserver Configuratie

Zorg dat je webserver (Apache/Nginx) is geconfigureerd om PHP te draaien. Voor Laragon/XAMPP/WAMP is dit meestal al ingesteld.

### 5. Bestandsstructuur

Zorg dat de volgende structuur bestaat:
```
project waar is dat feetje/
├── api/
│   ├── auth.php
│   ├── activiteiten.php
│   ├── deelnemers.php
│   ├── uitnodigingen.php
│   ├── notificaties.php
│   └── weer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── api.js
│       ├── auth.js
│       ├── kalender.js
│       ├── activiteiten.js
│       ├── notificaties.js
│       └── app.js
├── classes/
│   ├── Gebruiker.php
│   ├── Activiteit.php
│   ├── Uitnodiging.php
│   └── Notificatie.php
├── config/
│   └── database.php
├── database.sql
├── index.php
└── README.md
```

## Gebruik

1. Start je webserver (bijv. via Laragon)
2. Open de applicatie in je browser: `http://localhost/project waar is dat feetje/`
3. Registreer een nieuw account of log in
4. Begin met het aanmaken van activiteiten!

## Architectuur

### Backend (PHP)

- **Database Class**: Singleton pattern voor database connectie
- **Gebruiker Class**: Beheert gebruikers en authenticatie
- **Activiteit Class**: Beheert activiteiten CRUD operaties
- **Uitnodiging Class**: Beheert uitnodigingen voor gasten
- **Notificatie Class**: Beheert notificaties voor gebruikers

### Frontend (JavaScript)

- **API Class**: Beheert alle API calls
- **AuthManager Class**: Beheert authenticatie en sessie
- **Kalender Class**: Toont activiteiten in kalenderweergave
- **ActiviteitManager Class**: Beheert activiteiten weergave en filters
- **NotificatieManager Class**: Beheert notificaties

### Database Structuur

- `gebruikers`: Gebruikersgegevens
- `activiteiten`: Activiteiten informatie
- `deelnemers`: Veel-op-veel relatie tussen gebruikers en activiteiten
- `uitnodigingen`: Uitnodigingen voor gasten
- `notificaties`: Notificaties voor gebruikers

## Features in Detail

### Activiteiten
- Titel, beschrijving, datum, tijd, locatie
- Soort: binnen of buiten
- Status: gepland, geannuleerd, voltooid
- Organisator en deelnemers
- Opmerkingen

### Weersinformatie
- Automatisch opgehaald voor buitenactiviteiten
- Toont temperatuur, wind, vochtigheid
- Vereist OpenWeatherMap API key

### Notificaties
- Automatisch bij nieuwe activiteiten
- Bij wijzigingen in activiteiten
- Bij uitnodigingen
- Bij annuleringen

## Veiligheid

- Wachtwoorden worden gehashed met `password_hash()`
- Prepared statements tegen SQL injection
- Sessiebeheer voor authenticatie
- Input validatie

## Browser Ondersteuning

- Chrome (laatste versie)
- Firefox (laatste versie)
- Safari (laatste versie)
- Edge (laatste versie)

## Licentie

Dit project is gemaakt voor educatieve doeleinden.

## Ondersteuning

Voor vragen of problemen, raadpleeg de projectwijzer of neem contact op met je docent.

