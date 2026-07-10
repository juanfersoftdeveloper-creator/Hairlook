<?php
/**
 * Script de ayuda (one‑off) para re-hashear la contraseña de un profesional.
 * Uso (CLI): php rehash_profesional.php correo@ejemplo.com "contraseña_plana"
 * Nota: elimina este archivo después de usarlo.
 */

require_once __DIR__ . '/app/bootstrap.php';

if (php_sapi_name() !== 'cli') {
    echo "Este script debe ejecutarse desde la línea de comandos.\n";
    exit(1);
}

if ($argc < 3) {
    echo "Uso: php rehash_profesional.php correo@ejemplo.com \"contraseña_plana\"\n";
    exit(1);
}

$correo = $argv[1];
$plaintext = $argv[2];

try {
    $conn = getConnection();
    $hash = password_hash($plaintext, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE profesional SET Contrasena = ? WHERE Correo = ?");
    $stmt->execute([$hash, $correo]);

    if ($stmt->rowCount() > 0) {
        echo "Contraseña re-hasheada correctamente para: $correo\n";
        exit(0);
    } else {
        echo "No se encontró profesional con el correo: $correo\n";
        exit(2);
    }
} catch (Exception $e) {
    error_log("Error re-hasheando contraseña: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    exit(3);
}
