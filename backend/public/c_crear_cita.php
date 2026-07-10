<?php
/**
 * API JSON — Citas: crear cita
 * POST /backend/public/c_crear_cita.php
 */

header('Content-Type: application/json; charset=utf-8');
$allowed_origins = ['http://localhost:5173', 'http://localhost:5174'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:5173');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';
// require_once __DIR__ . '/../functions/funciones_barberia.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

$id_usuario = $input['id_usuario'] ?? null;
$id_profesional = $input['id_profesional'] ?? null;
$fecha = $input['fecha'] ?? null;
$hora = $input['hora'] ?? null;
$tipo = $input['tipo'] ?? null;

if (null === $id_usuario || null === $id_profesional || null === $fecha || null === $hora || null === $tipo) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
    exit;
}

try {
    $id_cita = crear_cita($id_usuario, $id_profesional, $fecha, $hora, $tipo);

    if ($id_cita) {
        echo json_encode(['ok' => true, 'id_cita' => $id_cita]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo crear la cita']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log('Error en c_crear_cita.php: ' . $e->getMessage());
}