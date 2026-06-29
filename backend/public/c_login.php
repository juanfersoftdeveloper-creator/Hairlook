<?php
/**
 * API JSON — Login de usuario o profesional.
 * POST /backend/public/c_login.php
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

$correo = $input['correo'] ?? '';
$password = $input['password'] ?? '';
$tipo = $input['tipo'] ?? 'cliente'; // 'cliente' o 'profesional'

if (empty($correo) || empty($password)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Correo y contraseña son requeridos']);
    exit;
}

try {
    $conn = getConnection();
    
    if ($tipo === 'profesional') {
        $stmt = $conn->prepare("SELECT id, nombres, apellidos, correo FROM profesional WHERE correo = :correo");
    } else {
        $stmt = $conn->prepare("SELECT id, nombres, apellidos, correo FROM usuario WHERE correo = :correo");
    }
    
    $stmt->execute([':correo' => $correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    // Verificar contraseña (asumiendo que está hasheada)
    // Por ahora verificamos directamente (en producción usar password_verify)
    if ($tipo === 'profesional') {
        $stmt = $conn->prepare("SELECT * FROM profesional WHERE correo = :correo AND password = :password");
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = :correo AND password = :password");
    }
    
    $stmt->execute([':correo' => $correo, ':password' => $password]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    // Login exitoso - crear sesión
    session_start();
    if ($tipo === 'profesional') {
        $_SESSION['profesional'] = $user;
    } else {
        $_SESSION['usuario'] = $user;
    }
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'id' => $user['id'],
            'nombre' => $user['nombres'],
            'correo' => $user['correo'],
            'tipo' => $tipo
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log($e->getMessage());
}
