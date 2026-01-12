<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../classes/Activiteit.php';

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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $weer_data = json_decode($response, true);
    echo json_encode([
        'success' => true,
        'weer' => [
            'temperatuur' => $weer_data['main']['temp'] ?? null,
            'beschrijving' => $weer_data['weather'][0]['description'] ?? null,
            'icon' => $weer_data['weather'][0]['icon'] ?? null,
            'wind' => $weer_data['wind']['speed'] ?? null,
            'vochtigheid' => $weer_data['main']['humidity'] ?? null
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Kon weerdata niet ophalen. Zorg dat je een geldige API key hebt.'
    ]);
}

