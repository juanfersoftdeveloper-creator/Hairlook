<?php
/**
 * API JSON — Agenda: obtener agenda de un profesional
 * GET /backend/public/c_agenda_profesional.php?id_profesional=X&solo_pendientes=true
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
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$id_profesional = $_GET['id_profesional'] ?? null;
$solo_pendientes = isset($_GET['solo_pendientes']) ? $_GET['solo_pendientes'] === 'true' : true;

if (null === $id_profesional) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_profesional requerido']);
    exit;
}

try {
    $citas = traer_citas_profesional((int)$id_profesional, (bool)$solo_pendientes);
    echo json_encode(['ok' => true, 'data' => $citas]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log('Error en c_agenda_profesional.php: ' . $e->getMessage());
}
