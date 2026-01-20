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

$method = $_SERVER['REQUEST_METHOD'];
$activiteit_id = $_GET['activiteit_id'] ?? null;
$gebruiker_id = $_SESSION['user_id'];

if (!$activiteit_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geen activiteit ID opgegeven']);
    exit;
}

$activiteit = new Activiteit($activiteit_id);

switch ($method) {
    case 'POST':
        // Aanmelden
        $result = $activiteit->aanmelden($gebruiker_id);
        echo json_encode($result);
        break;
    
    case 'DELETE':
        // Afmelden
        $result = $activiteit->afmelden($gebruiker_id);
        echo json_encode($result);
        break;
    
    case 'GET':
        // Haal deelnemers op
        $deelnemers = $activiteit->getDeelnemers();
        echo json_encode(['success' => true, 'deelnemers' => $deelnemers]);
        break;
}

