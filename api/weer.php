<?php
header('Content-Type: application/json');
// Laad autoloader (laadt automatisch alle classes)
require_once __DIR__ . '/../config/autoload.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Niet geautoriseerd']);
    exit;
}

$activiteit_id = $_GET['activiteit_id'] ?? null;

if (!$activiteit_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geen activiteit ID opgegeven']);
    exit;
}

$activiteit = new Activiteit($activiteit_id);

if ($activiteit->getSoort() !== 'buiten') {
    echo json_encode(['success' => false, 'message' => 'Alleen voor buitenactiviteiten']);
    exit;
}

// Haal weer data op via OpenWeatherMap API
$api_key = '4695c313ef6bebfc07fc0a7fd06c3adf';
$locatie = urlencode($activiteit->getLocatie());
$datum = $activiteit->getDatum();

// Voor nu gebruiken we huidige weer, voor forecast moet je de forecast API gebruiken
$url = "https://api.openweathermap.org/data/2.5/weather?q={$locatie}&appid={$api_key}&units=metric&lang=nl";

// Controleer of cURL beschikbaar is
if (!function_exists('curl_init')) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL is niet beschikbaar op deze server. Neem contact op met je hosting provider.'
    ]);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout van 10 seconden
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Controleer op cURL fouten
if ($curl_error) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL fout: ' . $curl_error
    ]);
    exit;
}

if ($http_code === 200) {
    $weer_data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Fout bij decoderen weerdata: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    // Controleer of data aanwezig is
    if (!isset($weer_data['main']) || !isset($weer_data['weather'][0])) {
        echo json_encode([
            'success' => false,
            'message' => 'Onvolledige weerdata ontvangen'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'weer' => [
            'temperatuur' => $weer_data['main']['temp'] ?? null,
            'beschrijving' => $weer_data['weather'][0]['description'] ?? null,
            'icon' => $weer_data['weather'][0]['icon'] ?? null,
            'wind' => $weer_data['wind']['speed'] ?? null,
            'vochtigheid' => $weer_data['main']['humidity'] ?? null,
            'locatie' => $weer_data['name'] ?? $activiteit->getLocatie()
        ]
    ]);
} else {
    // Haal error details op
    $error_data = json_decode($response, true);
    $error_message = 'Kon weerdata niet ophalen';
    
    if (isset($error_data['message'])) {
        $error_message = $error_data['message'];
    } elseif ($http_code === 401) {
        $error_message = 'Ongeldige API key. Controleer je OpenWeatherMap API key.';
    } elseif ($http_code === 404) {
        $error_message = 'Locatie niet gevonden: ' . $activiteit->getLocatie();
    } else {
        $error_message = "HTTP Error $http_code: Kon weerdata niet ophalen";
    }
    
    echo json_encode([
        'success' => false,
        'message' => $error_message,
        'http_code' => $http_code
    ]);
}

