<?php
/**
 * API JSON — Citas: servicios disponibles
 * GET /backend/public/c_servicios.php
 */

header('Content-Type: application/json; charset=utf-8');
$allowed_origins = ['http://localhost:5173', 'http://localhost:5174'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:5173');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true'); // para mantener sesiones si fuese necesario

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Cargamos el bootstrap que incluye las funciones de barbería
require_once __DIR__ . '/../app/bootstrap.php';
// Si bootstrap.php NO incluye las funciones, descomenta la siguiente línea:
// require_once __DIR__ . '/../functions/funciones_barberia.php';

// Si el método no es GET, responder 405
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

// Llamamos a la función que ya existe en funciones_barberia.php
$servicios = traer_servicios();

echo json_encode(['ok' => true, 'data' => $servicios]);