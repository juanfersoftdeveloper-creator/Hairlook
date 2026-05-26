<?php
/**
 * HairLook - Sistema de Gestión para Barberías
 * Funciones principales del backend en PHP 8.x
 *
 * Este archivo contiene todas las funciones de autenticación y gestión de citas
 * para el sistema HairLook.
 */

// === CONEXIÓN A LA BASE DE DATOS ===
function getConnection(): PDO {
    try {
        $host = 'localhost';
        $dbname = 'hairlook';
        $username = 'root';
        $password = '';

        $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        throw new Exception("No se pudo conectar a la base de datos");
    }
}

// === FUNCIONES DE AUTENTICACIÓN ===

/**
 * Registra un nuevo usuario con contraseña hasheada
 */
function registrar_usuario(string $nombre, string $apellido, string $correo, string $contraseña, string $rol = 'cliente'): bool {
    try {
        $conn = getConnection();

        // Validar correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de correo inválido");
        }

        // Verificar que el correo no exista
        $stmt = $conn->prepare("SELECT ID_Usuario FROM usuario WHERE Correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            throw new Exception("El correo ya está registrado");
        }

        // Hashear la contraseña
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $conn->prepare("
            INSERT INTO usuario (Nombre, Apellido, Correo, Contraseña, Rol, Fecha_registro)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([$nombre, $apellido, $correo, $hash, $rol]);
    } catch (PDOException $e) {
        error_log("Error al registrar usuario: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Iniciar sesión verificando credenciales
 */
function iniciar_sesion(string $correo, string $contraseña): ?array {
    try {
        $conn = getConnection();

        // Buscar usuario por correo
        $stmt = $conn->prepare("
            SELECT ID_Usuario, Nombre, Apellido, Correo, Rol, Contraseña
            FROM usuario
            WHERE Correo = ?
        ");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null; // Usuario no encontrado
        }

        // Verificar contraseña
        if (!password_verify($contraseña, $usuario['Contraseña'])) {
            return null; // Contraseña incorrecta
        }

        // Eliminar la contraseña del array antes de devolverlo
        unset($usuario['Contraseña']);

        // Iniciar sesión
        $_SESSION['usuario'] = $usuario;

        return $usuario;
    } catch (PDOException $e) {
        error_log("Error al iniciar sesión: " . $e->getMessage());
        return null;
    }
}

/**
 * Cerrar sesión destruyendo la sesión
 */
function cerrar_sesion(): void {
    session_start();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        setcookie(session_name(), '', time() - 3600);
    }
    session_destroy();
}

/**
 * Verificar el rol del usuario para restringir acceso
 */
function verificar_rol(string $rolRequerido): bool {
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['Rol'] !== $rolRequerido) {
        return false;
    }
    return true;
}

/**
 * Validar correo - comprueba formato y existencia
 */
function validar_correo(string $correo): array {
    $resultados = [
        'valido' => false,
        'mensaje' => '',
        'disponible' => false
    ];

    try {
        $conn = getConnection();

        // Validar formato
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $resultados['mensaje'] = "Formato de correo inválido";
            return $resultados;
        }

        $resultados['valido'] = true;
        $resultados['mensaje'] = "Formato de correo válido";

        // Verificar disponibilidad
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE Correo = ?");
        $stmt->execute([$correo]);
        $existe = $stmt->fetchColumn();

        $resultados['disponible'] = ($existe == 0);
        $resultados['mensaje'] = $existe ? "El correo ya está registrado" : "Correo disponible";

        return $resultados;
    } catch (PDOException $e) {
        error_log("Error al validar correo: " . $e->getMessage());
        $resultados['mensaje'] = "Error del sistema";
        return $resultados;
    }
}

// === FUNCIONES DE GESTIÓN DE CITAS ===

/**
 * Crea una nueva cita en la base de datos
 */
function crear_cita(int $id_usuario, int $id_profesional, string $fecha, string $hora, string $tipo = 'local'): ?int {
    try {
        $conn = getConnection();

        $fecha_hora = $fecha . ' ' . $hora;
        $estado = ($tipo == 'domicilio') ? 'programada' : 'confirmada';

        $stmt = $conn->prepare("
            INSERT INTO cita (Fecha_hora, Estado, ID_Usuario, ID_Profesional)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$fecha_hora, $estado, $id_usuario, $id_profesional]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al crear cita: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene las citas de un profesional
 */
function obtener_agenda_barbero(int $id_profesional, bool $solo_pendientes = true): array {
    try {
        $conn = getConnection();

        $query = "
            SELECT c.ID_Cita, c.Fecha_hora, c.Estado,
                   u.Nombre as Cliente, u.Teléfono as Telefono_cliente,
                   u.ID_Usuario
            FROM cita c
            JOIN usuario u ON c.ID_Usuario = u.ID_Usuario
            WHERE c.ID_Profesional = ?
        ";

        $params = [$id_profesional];

        if ($solo_pendientes) {
            $query .= " AND c.Estado IN ('confirmada', 'programada')";
        }

        $query .= " ORDER BY c.Fecha_hora ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener servicios para cada cita
        foreach ($citas as &$cita) {
            $stmt2 = $conn->prepare("
                SELECT s.Nombre, dc.Precio_aplicado
                FROM detalle_cita dc
                JOIN servicio s ON dc.ID_Servicio = s.ID_Servicio
                WHERE dc.ID_Cita = ?
            ");
            $stmt2->execute([$cita['ID_Cita']]);
            $cita['Servicios'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $citas;
    } catch (PDOException $e) {
        error_log("Error al obtener agenda: " . $e->getMessage());
        return [];
    }
}

/**
 * Busca barberos por ubicación (simulado)
 */
function buscar_barberos_por_ubicacion(?float $latitud, ?float $longitud, ?string $ciudad, float $radio_km = 10.0): array {
    try {
        $conn = getConnection();

        $query = "
            SELECT p.ID_Profesional, p.Nombre, p.Especialidad,
                   p.Telefono, p.Correo
            FROM profesional p
            WHERE 1=1
        ";

        $params = [];

        if ($ciudad) {
            // Filtro simulado por ciudad
            $query .= " AND /* Filtro por ciudad: " . $ciudad . " */ 1=1";
        }

        $query .= " ORDER BY p.Nombre ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $barberos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agregar disponibilidad
        foreach ($barberos as &$barbero) {
            $stmt2 = $conn->prepare("
                SELECT Dia_semana, Hora_inicial, Hora_fin
                FROM disponibilidad
                WHERE ID_Profesional = ?
                ORDER BY FIELD(Dia_semana, 'Lunes', 'Martes', 'Miércoles',
                              'Jueves', 'Viernes', 'Sábado', 'Domingo')
            ");
            $stmt2->execute([$barbero['ID_Profesional']]);
            $barbero['Disponibilidad'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }

        return $barberos;
    } catch (PDOException $e) {
        error_log("Error al buscar barberos: " . $e->getMessage());
        return [];
    }
}

/**
 * Cancela una cita cambiando su estado a 'cancelada'
 */
function cancelar_cita(int $id_cita): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("UPDATE cita SET Estado = 'cancelada' WHERE ID_Cita = ?");
        $stmt->execute([$id_cita]);

        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error al cancelar cita: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene la lista de servicios disponibles
 */
function obtener_servicios(): array {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            SELECT ID_Servicio, Nombre, Descripción, Precio, Duracion_min
            FROM servicio
            ORDER BY Precio ASC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener servicios: " . $e->getMessage());
        return [];
    }
}

/**
 * Agrega un servicio a una cita existente
 */
function agregar_servicio_a_cita(int $id_cita, int $id_servicio, float $precio): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            INSERT INTO detalle_cita (ID_Cita, ID_Servicio, Precio_aplicado)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$id_cita, $id_servicio, $precio]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al agregar servicio: " . $e->getMessage());
        return false;
    }
}

// === INICIALIZACIÓN DE SESIÓN ===
session_start();