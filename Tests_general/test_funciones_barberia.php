<?php
/**
 * Pruebas básicas para las funciones de barbería de HairLook.
 *
 * Ejecutar desde la línea de comandos:
 *   php test_funciones_barberia.php
 *
 * Ajusta los datos de prueba según tu base de datos.
 */

require_once __DIR__ . '/../Funciones Hairlook/funciones_barberia.php';

function printResult(string $name, bool $passed, string $message = ''): void {
    $status = $passed ? "OK" : "FAIL";
    echo sprintf("%-30s [%s] %s\n", $name, $status, $message);
}

function runTest(string $name, callable $callback): void {
    try {
        $result = $callback();
        $passed = $result !== false;
        printResult($name, $passed, $passed ? '' : 'Resultado false');
    } catch (Throwable $e) {
        printResult($name, false, $e->getMessage());
    }
}

echo "HairLook - Test de funciones\n";
echo str_repeat('=', 40) . "\n";

runTest('Conexión a base de datos', function () {
    $conn = getConnection();
    return $conn instanceof PDO;
});

runTest('Validar correo válido', function () {
    $data = validar_correo('prueba@example.com');
    return is_array($data) && isset($data['valido'], $data['disponible']);
});

runTest('Obtener servicios', function () {
    $servicios = obtener_servicios();
    return is_array($servicios);
});

runTest('Traer profesionales', function () {
    $profesionales = traer_profesionales();
    return is_array($profesionales);
});

runTest('Traer usuarios', function () {
    $usuarios = traer_usuarios();
    return is_array($usuarios);
});

runTest('Traer citas generales', function () {
    $citas = traer_citas();
    return is_array($citas);
});

runTest('Buscar barberos por ubicación', function () {
    $barberos = buscar_barberos_por_ubicacion(null, null, null);
    return is_array($barberos);
});

// Pruebas opcionales que pueden modificar la base de datos:
// runTest('Registrar usuario de prueba', function () {
//     return registrar_usuario('Usuario Test', 'testusuario@example.com', 'Pass1234');
// });
// runTest('Crear cita de prueba', function () {
//     return crear_cita(1, 1, '2026-06-10', '10:00:00') !== null;
// });

// Prueba de notificación (retorna false si la cita no existe)
runTest('Notificar cita de prueba', function () {
    return notificar_cita(1);
});

echo str_repeat('=', 40) . "\n";
echo "Pruebas finalizadas. Ajusta los tests de inserción si quieres cubrir operaciones de escritura.\n";
