# OOP Concepten Uitleg - "Waar is dat feestje?" Project

Dit document legt alle Object-Oriented Programming (OOP) concepten uit die gebruikt worden in dit project. Alle uitleg is in begrijpelijke taal voor beginnende developers.

---

## 📚 Inhoudsopgave

1. [OOP Basis: Classes, Extends en Static](#1-oop-basis-classes-extends-en-static)
2. [Interfaces en Abstract Classes](#2-interfaces-en-abstract-classes)
3. [Database Class (Singleton Pattern)](#3-database-class-singleton-pattern)
4. [Autoloading](#4-autoloading)

---

## 1. OOP Basis: Classes, Extends en Static

### Wat is een Class?

Een **class** is een blauwdruk (template) voor het maken van objecten. Het is zoals een recept: het beschrijft wat je nodig hebt en hoe je het maakt, maar het is nog niet het echte gerecht.

**Voorbeeld uit ons project:**
```php
class Activiteit extends BaseModel {
    private $titel;
    private $datum;
    // ... meer eigenschappen
}
```

**Eenvoudige uitleg:**
- Een class is een verzameling van eigenschappen (variabelen) en acties (methodes)
- Je kunt meerdere objecten maken van één class (zoals meerdere activiteiten)
- Elke activiteit heeft zijn eigen titel, datum, etc.

**Waar te vinden in het project:**
- `classes/Activiteit.php` - Class voor activiteiten
- `classes/Gebruiker.php` - Class voor gebruikers
- `classes/Notificatie.php` - Class voor notificaties
- `classes/Database.php` - Class voor database connectie

---

### Wat is Extends (Overerving)?

**Extends** betekent dat een class alle eigenschappen en methodes overneemt van een andere class. Het is zoals erfelijkheid: een kind krijgt eigenschappen van de ouders.

**Voorbeeld uit ons project:**
```php
// BaseModel is de ouder class
abstract class BaseModel {
    protected $id;
    protected $db;
    
    public function getId() {
        return $this->id;
    }
}

// Activiteit is de kind class - erft alles van BaseModel
class Activiteit extends BaseModel {
    // Activiteit heeft automatisch $id, $db en getId() van BaseModel
    private $titel;
    private $datum;
}
```

**Eenvoudige uitleg:**
- `BaseModel` is de ouder (parent class)
- `Activiteit` is het kind (child class)
- `Activiteit` krijgt automatisch alles van `BaseModel`
- Dit voorkomt code duplicatie - je schrijft gemeenschappelijke code één keer

**Voordelen:**
- Minder code schrijven (DRY principe: Don't Repeat Yourself)
- Makkelijker onderhouden
- Consistente structuur

**Waar te vinden in het project:**
- `classes/BaseModel.php` - De ouder class
- `classes/Activiteit.php` - Erft van BaseModel (regel 11: `extends BaseModel`)
- `classes/Gebruiker.php` - Erft ook van BaseModel (regel 10: `extends BaseModel`)

---

### Wat is Static?

Een **static** methode of eigenschap hoort bij de class zelf, niet bij een specifiek object. Je kunt het gebruiken zonder eerst een object te maken.

**Verschil tussen normaal en static:**

```php
// NORMAAL: Je moet eerst een object maken
$activiteit = new Activiteit(1);  // Maak object
$titel = $activiteit->getTitel(); // Gebruik object

// STATIC: Je gebruikt de class direct
$alleActiviteiten = Activiteit::getAll(); // Geen object nodig!
```

**Voorbeeld uit ons project:**
```php
class Gebruiker extends BaseModel {
    // Static methode - kan gebruikt worden zonder object
    public static function registreer($voornaam, $achternaam, $email, $wachtwoord) {
        // ... registratie code
    }
    
    // Static methode - haal alle gebruikers op
    public static function getAll() {
        // ... code om alle gebruikers op te halen
    }
}

// Gebruik (zonder object te maken):
Gebruiker::registreer('Jan', 'Jansen', 'jan@example.com', 'wachtwoord123');
$alleGebruikers = Gebruiker::getAll();
```

**Eenvoudige uitleg:**
- **Normale methode**: Werkt met één specifiek object (bijv. één activiteit)
- **Static methode**: Werkt met de class zelf, niet met een specifiek object
- Static methodes zijn handig voor acties die niet bij één object horen

**Wanneer gebruik je static?**
- Voor acties die bij de class horen, niet bij één object
- Voor het ophalen van meerdere items (bijv. `getAll()`)
- Voor utility functies (bijv. `registreer()`, `login()`)

**Waar te vinden in het project:**
- `classes/Gebruiker.php`:
  - `Gebruiker::registreer()` - Regel 41
  - `Gebruiker::login()` - Regel 87
  - `Gebruiker::logout()` - Regel 126
  - `Gebruiker::getAll()` - Regel 135
  - `Gebruiker::getCurrentUser()` - Regel 144

- `classes/Activiteit.php`:
  - `Activiteit::getAll()` - Regel 217
  - `Activiteit::getVoorKalender()` - Regel 263

- `classes/Database.php`:
  - `Database::getInstance()` - Regel 42 (Singleton pattern)

---

## 2. Interfaces en Abstract Classes

### Wat is een Interface?

Een **interface** is een contract dat classes moeten volgen. Het zegt WELKE methodes een class moet hebben, maar niet HOE ze geïmplementeerd moeten worden.

**Vergelijking:**
- Interface = Contract (je MOET deze methodes hebben)
- Class = De implementatie (zo doe je het echt)

**Voorbeeld uit ons project:**
```php
// Interface definieert WAT er moet zijn
interface Validatable {
    public function validate($data);
    public function isValid($data);
}

// Class implementeert HOE het werkt
class Activiteit extends BaseModel implements Validatable {
    // MOET deze methode hebben (van interface)
    public function validate($data) {
        $errors = [];
        if (empty($data['titel'])) {
            $errors[] = 'Titel is verplicht';
        }
        return $errors;
    }
    
    // MOET deze methode hebben (van interface)
    public function isValid($data) {
        $errors = $this->validate($data);
        return empty($errors);
    }
}
```

**Eenvoudige uitleg:**
- Een interface is een lijst van methodes die een class MOET hebben
- Het zorgt ervoor dat verschillende classes op dezelfde manier gebruikt kunnen worden
- Het maakt code voorspelbaarder en makkelijker te onderhouden

**Waarom gebruiken we interfaces?**
- **Consistentie**: Alle classes die `Validatable` implementeren hebben `validate()`
- **Polymorfisme**: Je kunt verschillende classes op dezelfde manier gebruiken
- **Documentatie**: Het maakt duidelijk wat een class kan doen

**Waar te vinden in het project:**
- `classes/Validatable.php` - Interface voor validatie
- `classes/Saveable.php` - Interface voor opslaan/updaten/verwijderen
- `classes/BaseModel.php` - Implementeert beide interfaces (regel 9)
- `classes/Activiteit.php` - Erft interfaces via BaseModel
- `classes/Gebruiker.php` - Erft interfaces via BaseModel

**Gebruik in de code:**
```php
// Je kunt verschillende classes op dezelfde manier gebruiken
function valideerData(Validatable $object, $data) {
    if ($object->isValid($data)) {
        echo "Data is geldig!";
    } else {
        echo "Data is ongeldig!";
    }
}

// Werkt met zowel Activiteit als Gebruiker (omdat beide Validatable zijn)
$activiteit = new Activiteit();
valideerData($activiteit, $data);

$gebruiker = new Gebruiker();
valideerData($gebruiker, $data);
```

---

### Wat is een Abstract Class?

Een **abstract class** is een class die niet direct gebruikt kan worden. Je moet er een andere class van maken (extends) voordat je het kunt gebruiken.

**Vergelijking:**
- Abstract class = Onvolledig recept (je kunt het niet direct gebruiken)
- Normale class = Compleet recept (je kunt het direct gebruiken)

**Voorbeeld uit ons project:**
```php
// Abstract class - kan niet direct gebruikt worden
abstract class BaseModel implements Validatable, Saveable {
    protected $id;
    protected $db;
    
    // Abstracte methode - MOET geïmplementeerd worden door child classes
    abstract protected function loadFromDatabase($id);
    abstract public function validate($data);
    
    // Normale methode - kan gebruikt worden door child classes
    public function getId() {
        return $this->id;
    }
}

// Normale class - kan direct gebruikt worden
class Activiteit extends BaseModel {
    // MOET loadFromDatabase() implementeren (van abstract class)
    protected function loadFromDatabase($id) {
        // ... implementatie
    }
    
    // MOET validate() implementeren (van abstract class)
    public function validate($data) {
        // ... implementatie
    }
}
```

**Eenvoudige uitleg:**
- Abstract class = Basis class met gemeenschappelijke code
- Abstracte methode = Methode die MOET geïmplementeerd worden door child classes
- Je kunt geen object maken van een abstract class: `new BaseModel()` geeft een fout

**Waarom gebruiken we abstract classes?**
- **Code delen**: Gemeenschappelijke code in één plek
- **Structuur**: Zorgt ervoor dat alle child classes dezelfde basis hebben
- **Flexibiliteit**: Child classes kunnen hun eigen implementatie hebben

**Verschil tussen Interface en Abstract Class:**

| Interface | Abstract Class |
|-----------|----------------|
| Alleen method signatures | Kan ook code bevatten |
| Geen eigenschappen | Kan eigenschappen hebben |
| Meerdere interfaces mogelijk | Maar één abstract class |
| Alle methodes moeten public | Kan protected/private hebben |

**Waar te vinden in het project:**
- `classes/BaseModel.php` - Abstract class (regel 9: `abstract class BaseModel`)
- Abstracte methodes:
  - `loadFromDatabase($id)` - Regel 27
  - `validate($data)` - Regel 33
  - `save($data)` - Regel 40
  - `update($data)` - Regel 46
  - `delete()` - Regel 52

---

## 3. Database Class (Singleton Pattern)

### Wat is de Database Class?

De **Database class** zorgt voor de connectie met de database. Het gebruikt het **Singleton Pattern**, wat betekent dat er maar één database connectie kan zijn.

**Wat is Singleton Pattern?**
- Er kan maar één instantie (object) van de class zijn
- Als je de connectie opvraagt, krijg je altijd dezelfde
- Dit voorkomt meerdere database connecties (wat slecht is voor performance)

**Voorbeeld uit ons project:**
```php
class Database {
    // Static eigenschap - hoort bij de class, niet bij een object
    private static $instance = null;
    private $connection;
    
    // Private constructor - voorkomt dat je direct een object maakt
    private function __construct() {
        $this->connection = new PDO(/* ... database config ... */);
    }
    
    // Static methode - de enige manier om de database te krijgen
    public static function getInstance() {
        // Als er nog geen instantie is, maak er een
        if (self::$instance === null) {
            self::$instance = new self();
        }
        // Geef de bestaande (of nieuwe) instantie terug
        return self::$instance;
    }
    
    // Haal de database connectie op
    public function getConnection() {
        return $this->connection;
    }
}
```

**Eenvoudige uitleg:**
- **Singleton** = Er is maar één (single) instantie
- Je kunt niet `new Database()` doen (constructor is private)
- Je moet `Database::getInstance()` gebruiken
- Elke keer dat je `getInstance()` aanroept, krijg je dezelfde connectie

**Gebruik in het project:**
```php
// Overal in het project gebruik je het zo:
$db = Database::getInstance()->getConnection();

// Of in classes:
$this->db = Database::getInstance()->getConnection();
```

**Waarom Singleton?**
- **Performance**: Eén connectie is sneller dan meerdere
- **Resource management**: Database connecties zijn duur, je wilt er niet te veel
- **Consistentie**: Alle code gebruikt dezelfde connectie

**Waar te vinden in het project:**
- `config/database.php` - De Database class
- Gebruikt in:
  - `classes/BaseModel.php` - Regel 17
  - `classes/Activiteit.php` - Regel 218, 264
  - `classes/Gebruiker.php` - Regel 42, 88, 136
  - `classes/Notificatie.php` - Regel 12
  - `classes/Uitnodiging.php` - Regel 19, 69, 94

---

## 4. Autoloading

### Wat is Autoloading?

**Autoloading** betekent dat PHP automatisch classes laadt wanneer je ze nodig hebt. Je hoeft niet meer handmatig `require_once` of `include` te gebruiken!

**Voor autoloading (oud):**
```php
// Je moest elke class handmatig laden
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Activiteit.php';
require_once __DIR__ . '/../classes/Gebruiker.php';

// Nu kun je de classes gebruiken
$activiteit = new Activiteit();
```

**Met autoloading (nieuw):**
```php
// Laad alleen de autoloader
require_once __DIR__ . '/../config/autoload.php';

// PHP laadt automatisch de classes wanneer je ze nodig hebt
$activiteit = new Activiteit(); // Automatisch geladen!
$gebruiker = new Gebruiker();   // Automatisch geladen!
```

**Hoe werkt het?**

1. Je laadt de autoloader: `require_once 'config/autoload.php'`
2. Wanneer je een class gebruikt (bijv. `new Activiteit()`)
3. Zoekt PHP eerst of de class al geladen is
4. Als niet, roept PHP de autoload functie aan
5. De autoload functie zoekt het bestand (`classes/Activiteit.php`)
6. En laadt het automatisch

**Voorbeeld uit ons project:**
```php
// config/autoload.php
spl_autoload_register(function ($className) {
    $baseDir = __DIR__ . '/../classes/';
    $file = $baseDir . $className . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    
    // Speciale case voor Database class
    if ($className === 'Database') {
        $file = __DIR__ . '/database.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    throw new Exception("Class '$className' niet gevonden");
});
```

**Eenvoudige uitleg:**
- `spl_autoload_register()` = Registreer een functie die classes automatisch laadt
- De functie krijgt de class naam (bijv. "Activiteit")
- En zoekt het bestand (bijv. "classes/Activiteit.php")
- Laadt het bestand automatisch

**Voordelen:**
- ✅ Minder code (geen lange lijst van require_once)
- ✅ Minder fouten (geen vergeten require_once)
- ✅ Betere performance (laadt alleen wat nodig is)
- ✅ Makkelijker onderhouden

**Waar te vinden in het project:**
- `config/autoload.php` - De autoload configuratie
- Gebruikt in alle API bestanden:
  - `api/auth.php` - Regel 21
  - `api/activiteiten.php` - Regel 3
  - `api/notificaties.php` - Regel 3
  - `api/deelnemers.php` - Regel 3
  - `api/uitnodigingen.php` - Regel 3
  - `api/test.php` - Regel 6
  - `api/weer.php` - Regel 3

**Belangrijk:**
- Alle `require_once` statements voor classes zijn verwijderd
- Alleen de autoloader wordt geladen
- PHP laadt de rest automatisch wanneer nodig

---

## 📝 Samenvatting

### Wat hebben we geleerd?

1. **Classes**: Blauwdrukken voor objecten
2. **Extends**: Overerving - child classes krijgen eigenschappen van parent classes
3. **Static**: Methodes die bij de class horen, niet bij een object
4. **Interfaces**: Contracten die definiëren welke methodes een class moet hebben
5. **Abstract Classes**: Basis classes die niet direct gebruikt kunnen worden
6. **Singleton Pattern**: Zorgt voor één database connectie
7. **Autoloading**: Automatisch laden van classes wanneer nodig

### Project Structuur

```
project/
├── classes/
│   ├── BaseModel.php      (Abstract class)
│   ├── Activiteit.php     (Extends BaseModel, gebruikt interfaces)
│   ├── Gebruiker.php      (Extends BaseModel, gebruikt interfaces)
│   ├── Notificatie.php    (Normale class)
│   ├── Uitnodiging.php    (Normale class)
│   ├── Validatable.php    (Interface)
│   └── Saveable.php       (Interface)
├── config/
│   ├── database.php       (Database class - Singleton)
│   └── autoload.php       (Autoload configuratie)
└── api/
    └── ...                (Alle API bestanden gebruiken autoload)
```

---

## 🎯 Oefeningen om te begrijpen

1. **Class maken**: Maak een nieuwe class `Bericht` die van `BaseModel` erft
2. **Static methode**: Voeg een static methode `Bericht::getRecent()` toe
3. **Interface**: Laat `Bericht` de `Validatable` interface implementeren
4. **Autoloading**: Test of `new Bericht()` automatisch werkt zonder require_once

---

## 📚 Extra Resources

- [PHP OOP Documentatie](https://www.php.net/manual/en/language.oop5.php)
- [PHP Interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php)
- [PHP Abstract Classes](https://www.php.net/manual/en/language.oop5.abstract.php)
- [PHP Autoloading](https://www.php.net/manual/en/language.oop5.autoload.php)

---

**Laatste update**: Alle OOP concepten zijn geïmplementeerd en getest in het project!
