<?php
/**
 * API JSON — Login de usuario o profesional.
 * POST /backend/public/c_login.php
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
header('Access-Control-Allow-Credentials: true'); // Importante para mantener sesiones

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Inicializar sesión para que las funciones de login puedan usar $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargamos tu bootstrap (que asumo incluye tus funciones de barbería)
require_once __DIR__ . '/../app/bootstrap.php';

// Si bootstrap.php NO incluye las funciones, descomenta la siguiente línea:
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

$correo = $input['correo'] ?? '';
// CORRECCIÓN 1: Capturamos 'password' que es lo que manda React
$password = $input['password'] ?? $input['contrasena'] ?? ''; 
$tipo = $input['tipo'] ?? 'cliente'; // 'cliente' o 'profesional'

if (empty($correo) || empty($password)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Correo y contraseña son requeridos']);
    exit;
}

try {
    $usuarioLogueado = null;
    
    // Usamos las funciones de backend que ya manejan
    // los nombres correctos de BD y el password_verify()
    if ($tipo === 'profesional') {
        $usuarioLogueado = login_profesional($correo, $password);
    } else {
        $usuarioLogueado = iniciar_sesion($correo, $password);
    }
    
    if (!$usuarioLogueado) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Correo o contraseña incorrectos']);
        exit;
    }
    
    // Login exitoso
    echo json_encode([
        'ok' => true,
        'data' => [
            // Extraemos los IDs según el rol que se logueó
            'id' => $tipo === 'profesional' ? $usuarioLogueado['ID_Profesional'] : $usuarioLogueado['ID_Usuario'],
            'nombre' => $usuarioLogueado['Nombre'],
            'correo' => $usuarioLogueado['Correo'],
            'tipo' => $tipo
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
    error_log($e->getMessage());
}