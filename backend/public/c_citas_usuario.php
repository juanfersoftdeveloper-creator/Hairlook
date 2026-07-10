<?php
/**
 * API JSON — Citas: citas de un usuario
 * GET /backend/public/c_citas_usuario.php?id_usuario=X
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
header('Access-Control-Allow-Credentials: true'); // importante para cookies/sesion

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';
// require_once __DIR__ . '/../functions/funciones_barberia.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

// Verificar si hay sesión activa (ya sea de usuario o profesional)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$session_user_id = null;

if (isset($_SESSION['usuario'])) {
    $session_user_id = $_SESSION['usuario']['ID_Usuario'] ?? null;
}

// Obtener el id_usuario del query string
$id_usuario_param = $_GET['id_usuario'] ?? null;
if (!$id_usuario_param) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_usuario requerido']);
    exit;
}

// Valida que el id_usuario solicitado coincida con el de la sesión (si hay sesión de usuario)
if ($session_user_id !== null && (int)$id_usuario_param !== (int)$session_user_id) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

// Si no hay sesión activa, requerir autenticación
if ($session_user_id === null) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Sesión no activa']);
    exit;
}

// Llamar a la función que ya existe (traer_citas con id_usuario)
$citas = traer_citas((int)$id_usuario_param);

echo json_encode(['ok' => true, 'data' => $citas]);