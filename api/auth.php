<?php
// Zet error reporting uit voor productie (of gebruik error_log)
error_reporting(0);
ini_set('display_errors', 0);

// Stel JSON header in VOOR alles
header('Content-Type: application/json; charset=utf-8');

// CORS headers (als nodig)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Laad autoloader (laadt automatisch alle classes)
    require_once __DIR__ . '/../config/autoload.php';
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'POST':
            if ($action === 'register') {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Ongeldige JSON data');
                }
                
                $result = Gebruiker::registreer(
                    $data['voornaam'] ?? '',
                    $data['achternaam'] ?? '',
                    $data['email'] ?? '',
                    $data['wachtwoord'] ?? ''
                );
                echo json_encode($result);
            } elseif ($action === 'login') {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Ongeldige JSON data');
                }
                
                $result = Gebruiker::login(
                    $data['email'] ?? '',
                    $data['wachtwoord'] ?? ''
                );
                echo json_encode($result);
            } elseif ($action === 'logout') {
                $result = Gebruiker::logout();
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
            }
            break;
        
        case 'GET':
            if ($action === 'current') {
                $user = Gebruiker::getCurrentUser();
                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'user' => [
                            'id' => $user->getId(),
                            'voornaam' => $user->getVoornaam(),
                            'achternaam' => $user->getAchternaam(),
                            'email' => $user->getEmail(),
                            'rol' => $user->getRol()
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Niet ingelogd']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Ongeldige actie']);
            }
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Methode niet toegestaan']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Er is een fout opgetreden: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Er is een kritieke fout opgetreden. Controleer of alle bestanden correct zijn geïnstalleerd.'
    ]);
}

