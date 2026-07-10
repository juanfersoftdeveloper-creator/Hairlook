<?php
/**
 * API JSON — Calificaciones: obtener calificaciones de un profesional
 * GET /backend/public/c_calificaciones_profesional.php?id_profesional=X
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

if (null === $id_profesional) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_profesional requerido']);
    exit;
}

try {
    $calificaciones = traer_calificaciones_profesional((int)$id_profesional);
    echo json_encode(['ok' => true, 'data' => $calificaciones]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log('Error en c_calificaciones_profesional.php: ' . $e->getMessage());
}
