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

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $uitnodiging = new Uitnodiging();
        $result = $uitnodiging->uitnodigen(
            $data['activiteit_id'] ?? null,
            $data['email'] ?? '',
            $_SESSION['user_id']
        );
        echo json_encode($result);
        break;
    
    case 'PUT':
        // Accepteer uitnodiging
        $data = json_decode(file_get_contents('php://input'), true);
        $result = Uitnodiging::accepteerViaToken(
            $data['token'] ?? '',
            $_SESSION['user_id']
        );
        echo json_encode($result);
        break;
}

