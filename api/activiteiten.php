<?php
header('Content-Type: application/json');
// Laad autoloader (laadt automatisch alle classes)
require_once __DIR__ . '/../config/autoload.php';

// Check of gebruiker is ingelogd
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Niet geautoriseerd']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            $activiteit = new Activiteit($id);
            $deelnemers = $activiteit->getDeelnemers();
            echo json_encode([
                'success' => true,
                'activiteit' => [
                    'id' => $activiteit->getId(),
                    'titel' => $activiteit->getTitel(),
                    'beschrijving' => $activiteit->getBeschrijving(),
                    'datum' => $activiteit->getDatum(),
                    'tijd' => $activiteit->getTijd(),
                    'locatie' => $activiteit->getLocatie(),
                    'soort' => $activiteit->getSoort(),
                    'status' => $activiteit->getStatus(),
                    'opmerkingen' => $activiteit->getOpmerkingen(),
                    'organisator_id' => $activiteit->getOrganisatorId(),
                    'deelnemers' => $deelnemers
                ]
            ]);
        } elseif ($action === 'kalender') {
            $jaar = $_GET['jaar'] ?? date('Y');
            $maand = $_GET['maand'] ?? date('m');
            $activiteiten = Activiteit::getVoorKalender($jaar, $maand);
            echo json_encode(['success' => true, 'activiteiten' => $activiteiten]);
        } else {
            // Haal filters op
            $filters = [];
            if (isset($_GET['soort'])) $filters['soort'] = $_GET['soort'];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if (isset($_GET['zoekterm'])) $filters['zoekterm'] = $_GET['zoekterm'];
            if (isset($_GET['datum_van'])) $filters['datum_van'] = $_GET['datum_van'];
            if (isset($_GET['datum_tot'])) $filters['datum_tot'] = $_GET['datum_tot'];
            
            $activiteiten = Activiteit::getAll($filters);
            echo json_encode(['success' => true, 'activiteiten' => $activiteiten]);
        }
        break;
    
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $data['organisator_id'] = $_SESSION['user_id'];
        
        $activiteit = new Activiteit();
        $result = $activiteit->aanmaken($data);
        echo json_encode($result);
        break;
    
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geen ID opgegeven']);
            exit;
        }
        
        $activiteit = new Activiteit($id);
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $activiteit->bijwerken($data);
        echo json_encode($result);
        break;
    
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geen ID opgegeven']);
            exit;
        }
        
        $activiteit = new Activiteit($id);
        $result = $activiteit->verwijderen();
        echo json_encode($result);
        break;
}

