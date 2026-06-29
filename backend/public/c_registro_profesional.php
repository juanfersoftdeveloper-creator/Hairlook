<?php
/**
 * API JSON — registro de profesional.
 * POST /backend/public/c_registro_profesional.php
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
$correo              = $input['correo'] ?? '';
$password            = $input['password'] ?? '';
$confirmarPassword   = $input['confirmarPassword'] ?? $password;
$especialidad        = $input['especialidad'] ?? '';
$experiencia         = $input['experiencia'] ?? '';

try {
    $conn = getConnection();
    
    // Validar que el correo no exista
    $stmt = $conn->prepare("SELECT id FROM profesional WHERE correo = :correo");
    $stmt->execute([':correo' => $correo]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'El correo ya está registrado']);
        exit;
    }
    
    // Validar passwords
    if ($password !== $confirmarPassword) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Las contraseñas no coinciden']);
        exit;
    }
    
    // Insertar profesional
    $stmt = $conn->prepare("
        INSERT INTO profesional (nombres, apellidos, cedula, correo, password, especialidad, experiencia)
        VALUES (:nombres, :apellidos, :cedula, :correo, :password, :especialidad, :experiencia)
    ");
    
    $result = $stmt->execute([
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':cedula' => $cedula,
        ':correo' => $correo,
        ':password' => $password, // En producción usar password_hash
        ':especialidad' => $especialidad,
        ':experiencia' => $experiencia
    ]);
    
    if ($result) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'No se pudo registrar el profesional']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log($e->getMessage());
}
