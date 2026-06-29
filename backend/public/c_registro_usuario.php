<?php
/**
 * API JSON — registro de cliente (usuario).
 * POST /backend/public/c_registro_usuario.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

$nombres             = $input['nombres'] ?? '';
$apellidos           = $input['apellidos'] ?? '';
$cedula              = $input['cedula'] ?? '';
$fechaNacimiento     = $input['fechaNacimiento'] ?? '';
$direccion           = $input['direccion'] ?? '';
$correo              = $input['correo'] ?? '';
$password            = $input['password'] ?? '';
$confirmarPassword   = $input['confirmarPassword'] ?? $password;
$metodoPago          = $input['metodoPago'] ?? '';

$exito = registrar_usuario(
    $nombres,
    $apellidos,
    $cedula,
    $fechaNacimiento,
    $direccion,
    $correo,
    $password,
    $confirmarPassword,
    $metodoPago
);

if ($exito) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No se pudo registrar el usuario. Verifica los datos e intenta de nuevo.']);
}
