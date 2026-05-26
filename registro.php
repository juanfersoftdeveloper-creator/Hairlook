<?php
// Incluimos el archivo de funciones para poder usar 'registrar_usuario'
require_once 'funciones_barberia.php';

$mensaje = "";

// Verificar si el usuario presionó el botón de registrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'cliente';

    // Llamamos a la función registrar_usuario
    $exito = registrar_usuario($nombre, $apellido, $correo, $contraseña, $rol);

    if ($exito) {
        $mensaje = "<div style='color: green;'>¡Usuario registrado con éxito en la DB!</div>";
    } else {
        $mensaje = "<div style='color: red;'>Error al registrar el usuario. Verifica si el correo ya existe o el formato es incorrecto.</div>";
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
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: 0 auto; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background-color: #333; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        .btn:hover { background-color: #555; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registro HairLook</h2>
    
    <?php echo $mensaje; ?>

    <form action="registro.php" method="POST">
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
            <label>Apellido:</label>
            <input type="text" name="apellido" required>
        </div>
        <div class="form-group">
            <label>Correo Electrónico:</label>
            <input type="email" name="correo" required>
        </div>
        <div class="form-group">
            <label>Contraseña:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Rol:</label>
            <select name="rol">
                <option value="cliente">Cliente</option>
                <option value="barbero">Barbero</option>
                <option value="administrador">Administrador</option>
            </select>
        </div>
        <button type="submit" class="btn">Registrar Usuario</button>
    </form>
</div>

</body>
</html>