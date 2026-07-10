<?php
/**
 * API JSON — Calificaciones: crear calificación
 * POST /backend/public/c_calificar.php
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
$id_usuario = $input['id_usuario'] ?? null;
$id_profesional = $input['id_profesional'] ?? null;
$puntuacion = $input['puntuacion'] ?? null;
$comentario = $input['comentario'] ?? null;

if (null === $id_usuario || null === $id_profesional || null === $puntuacion) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
    exit;
}

// Validar que puntuacion sea 1-5
if (!is_numeric($puntuacion) || $puntuacion < 1 || $puntuacion > 5) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Puntuación debe ser entre 1 y 5']);
    exit;
}

try {
    $resultado = insertar_calificacion(
        (int)$id_profesional,
        (int)$id_usuario,
        (int)$puntuacion,
        $comentario
    );

    if ($resultado) {
        // Actualizar el rating del profesional después de insertar
        actualizar_rating_profesional((int)$id_profesional);
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No se pudo registrar la calificación']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log('Error en c_calificar.php: ' . $e->getMessage());
}
