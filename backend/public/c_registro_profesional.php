<?php
/**
 * API JSON — registro de profesional.
 * POST /backend/public/c_registro_profesional.php
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
// Allow cookies/sessions from the frontend when using credentials: 'include'
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

$nombres             = $input['nombres'] ?? '';
$apellidos           = $input['apellidos'] ?? '';
$cedula              = $input['cedula'] ?? '';
$correo              = $input['correo'] ?? '';
$password            = $input['password'] ?? '';
$confirmarPassword   = $input['confirmarPassword'] ?? $password;
$especialidad        = $input['especialidad'] ?? '';
// Accept either 'experiencia' (backend) or 'aniosExperiencia' (frontend) as source
$experiencia         = $input['experiencia'] ?? $input['aniosExperiencia'] ?? '';

try {
    $conn = getConnection();
    
    // Validar que el correo no exista (usar el nombre real de columna ID_Profesional y Correo)
    $stmt = $conn->prepare("SELECT ID_Profesional FROM profesional WHERE Correo = :correo");
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
    
    // Map frontend fields to the profesional table schema
    $nombre_completo = trim(($nombres . ' ' . $apellidos));
    // Hashear la contraseña antes de guardarla para que password_verify funcione al iniciar sesión
    $contrasena = password_hash($password, PASSWORD_DEFAULT);

    // Insertar profesional en columnas existentes: Nombre, Especialidad, Correo, Contrasena
    $stmt = $conn->prepare("INSERT INTO profesional (Nombre, Especialidad, Correo, Contrasena) VALUES (:nombre, :especialidad, :correo, :contrasena)");

    $result = $stmt->execute([
        ':nombre' => $nombre_completo,
        ':especialidad' => $especialidad,
        ':correo' => $correo,
        ':contrasena' => $contrasena
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
