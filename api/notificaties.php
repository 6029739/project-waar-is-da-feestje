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

$notificatie = new Notificatie();
$gebruiker_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'ongelezen') {
            $aantal = $notificatie->telOngelezen($gebruiker_id);
            echo json_encode(['success' => true, 'aantal' => $aantal]);
        } else {
            $alleen_ongelezen = isset($_GET['alleen_ongelezen']) && $_GET['alleen_ongelezen'] === 'true';
            $notificaties = $notificatie->getVoorGebruiker($gebruiker_id, $alleen_ongelezen);
            echo json_encode(['success' => true, 'notificaties' => $notificaties]);
        }
        break;
    
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id'])) {
            $notificatie->markeerAlsGelezen($data['id'], $gebruiker_id);
        } elseif (isset($data['alles'])) {
            $notificatie->markeerAllesAlsGelezen($gebruiker_id);
        }
        echo json_encode(['success' => true]);
        break;
}

