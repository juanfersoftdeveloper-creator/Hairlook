<?php
require_once __DIR__ . '/../app/bootstrap.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exito = registrar_usuario(
        $_POST['nombres'] ?? '',
        $_POST['apellidos'] ?? '',
        $_POST['cedula'] ?? '',
        $_POST['fechaNacimiento'] ?? '',
        $_POST['direccion'] ?? '',
        $_POST['correo'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirmarPassword'] ?? '',
        $_POST['metodoPago'] ?? ''
    );

    if ($exito) {
        $mensaje = "<div style='color: green;'>¡Usuario registrado con éxito en la DB!</div>";
    } else {
        $mensaje = "<div style='color: red;'>Error al registrar el usuario. Verifica los datos e intenta de nuevo.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HairLook - Registro de Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 480px; margin: 0 auto; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }
        .btn { background-color: #333; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background-color: #555; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registro HairLook</h2>

    <?php echo $mensaje; ?>

    <form action="registro.php" method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="nombres">Nombres</label>
                <input type="text" id="nombres" name="nombres" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" required>
            </div>
        </div>
        <div class="form-group">
            <label for="cedula">Cédula</label>
            <input type="text" id="cedula" name="cedula" required>
        </div>
        <div class="form-group">
            <label for="fechaNacimiento">Fecha de nacimiento</label>
            <input type="date" id="fechaNacimiento" name="fechaNacimiento" required>
        </div>
        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" required>
        </div>
        <div class="form-group">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="4">
            </div>
            <div class="form-group">
                <label for="confirmarPassword">Confirmar contraseña</label>
                <input type="password" id="confirmarPassword" name="confirmarPassword" required minlength="4">
            </div>
        </div>
        <div class="form-group">
            <label for="metodoPago">Método de pago preferido</label>
            <select id="metodoPago" name="metodoPago" required>
                <option value="">Selecciona…</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="digital">Digital</option>
            </select>
        </div>
        <button type="submit" class="btn">Registrar Usuario</button>
    </form>
</div>

</body>
</html>
