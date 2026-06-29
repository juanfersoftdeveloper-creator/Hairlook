<?php
// Pruebas muy simples para algunas funciones de HairLook.
// Ejecuta desde la terminal:
//   php test_simple_validar_correo.php

require_once __DIR__ . '/../app/bootstrap.php';

function printResult(string $name, bool $passed, string $message = ''): void {
    $status = $passed ? 'OK' : 'FAIL';
    echo sprintf("%-22s [%s] %s\n", $name, $status, $message);
}

function test_validar_correo(): void {
    $email = 'usuario.ejemplo+' . time() . '@example.com';
    echo "\nProbando validar_correo() con: $email\n";
    $result = validar_correo($email);

    if (!is_array($result)) {
        printResult('validar_correo', false, 'No devolvió array');
        return;
    }

    $valid = $result['valido'] ?? false;
    $available = $result['disponible'] ?? false;
    $message = $result['mensaje'] ?? '';

    printResult('validar_correo', $valid && $available, "mensaje: $message");
}

function test_registrar_usuario_y_login(): ?array {
    $email = 'usuario.test+' . time() . '@example.com';
    $password = 'Test1234!';
    $cedula = (string) (1000000000 + (time() % 900000000));

    echo "\nProbando registrar_usuario() con: $email\n";
    $created = registrar_usuario(
        'Usuario',
        'Test',
        $cedula,
        '1990-05-15',
        'Calle Test 123',
        $email,
        $password,
        $password,
        'efectivo'
    );
    printResult('registrar_usuario', $created, $created ? '' : 'registro fallido');

    if (!$created) {
        return null;
    }

    echo "Probando iniciar_sesion() con el usuario creado\n";
    $user = iniciar_sesion($email, $password);
    $ok = is_array($user) && isset($user['ID_Usuario']);
    printResult('iniciar_sesion', $ok, $ok ? 'login exitoso' : 'login fallido');

    return $ok ? $user : null;
}

function test_traer_usuarios(): void {
    echo "\nProbando traer_usuarios()\n";
    $users = traer_usuarios();
    $ok = is_array($users);
    printResult('traer_usuarios', $ok, $ok ? 'usuarios leídos' : 'falló lectura');
}

function test_traer_servicios(): void {
    echo "\nProbando traer_servicios()\n";
    $services = traer_servicios();
    $ok = is_array($services);
    printResult('traer_servicios', $ok, $ok ? 'servicios leídos' : 'falló lectura');
}

function test_crear_cita(array $user): void {
    echo "\nProbando crear_cita()\n";
    $profesionales = traer_profesionales();
    if (empty($profesionales) || !isset($profesionales[0]['ID_Profesional'])) {
        printResult('crear_cita', false, 'No hay profesionales registrados');
        return;
    }

    $profesionalId = $profesionales[0]['ID_Profesional'];
    $fecha = date('Y-m-d', strtotime('+1 day'));
    $hora = '10:00:00';
    $citaId = crear_cita($user['ID_Usuario'], $profesionalId, $fecha, $hora);
    $ok = !is_null($citaId);
    printResult('crear_cita', $ok, $ok ? "ID cita: $citaId" : 'falló creación');
}

// Ejecutar tests simples:

test_validar_correo();

$createdUser = test_registrar_usuario_y_login();

if ($createdUser !== null) {
    test_crear_cita($createdUser);
}

test_traer_usuarios();
test_traer_servicios();

exit(0);
