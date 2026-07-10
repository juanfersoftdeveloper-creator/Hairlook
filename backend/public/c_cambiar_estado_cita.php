<?php
/**
 * API JSON — Citas: cambiar estado de cita
 * POST /backend/public/c_cambiar_estado_cita.php
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

$id_cita = $input['id_cita'] ?? null;
$estado = $input['estado'] ?? null;

if (null === $id_cita || null === $estado) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
    exit;
}

// Opcional: validar que el estado sea uno de los permitidos
$estados_permitidos = ['pendiente', 'confirmada', 'completada', 'cancelada', 'rechazada'];
if (!in_array($estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    $resultado = actualizar_estado_cita((int)$id_cita, $estado);

    if ($resultado) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar el estado']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log('Error en c_cambiar_estado_cita.php: ' . $e->getMessage());
}