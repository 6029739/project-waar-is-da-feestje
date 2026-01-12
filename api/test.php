<?php
// Test endpoint om te controleren of alles werkt
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../classes/BaseModel.php';
    require_once __DIR__ . '/../classes/Gebruiker.php';
    
    // Test database connectie
    $db = Database::getInstance()->getConnection();
    
    echo json_encode([
        'success' => true,
        'message' => 'Alles werkt correct!',
        'database' => 'Connected',
        'classes' => 'Loaded'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fout: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Kritieke fout: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

