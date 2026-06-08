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
function registrar_usuario(string $nombre, string $correo, string $contrasena): bool {
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
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $conn->prepare("
            INSERT INTO usuario (Nombre, Correo, Contrasena, Fecha_registro)
            VALUES (?, ?, ?, NOW())
        ");

        return $stmt->execute([$nombre, $correo, $hash]);
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
function iniciar_sesion(string $correo, string $contrasena): ?array {
    try {
        $conn = getConnection();

        // Buscar usuario por correo
        $stmt = $conn->prepare("
            SELECT ID_Usuario, Nombre, Correo, Contrasena, Telefono, Foto_perfil, Fecha_registro
            FROM usuario
            WHERE Correo = ?
        ");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null; // Usuario no encontrado
        }

        // Verificar contraseña (la columna se llama 'Contrasena' sin tilde)
        if (!password_verify($contrasena, $usuario['Contrasena'])) {
            return null; // Contrasena incorrecta
        }

        // Eliminar la contraseña del array antes de devolverlo
        unset($usuario['Contrasena']);

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
    //session_start();
    
    if ($rolRequerido === 'barbero') {
        return isset($_SESSION['profesional']); // Verifica si es barbero
    }
    
    if ($rolRequerido === 'cliente') {
        return isset($_SESSION['usuario']); // Verifica si es cliente
    }
    return false;
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
                   u.Nombre as Cliente, u.Telefono as Telefono_cliente,
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
            SELECT ID_Servicio, Nombre, Descripcion, Precio, Duracion_min
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
//session_start();

/* ==== NUEVAS FUNCIONES SOLICITADAS ==== */

/**
 * Obtiene todos los usuarios registrados
 */
function traer_usuarios(): array {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT ID_Usuario, Nombre, Correo, Telefono, Foto_perfil FROM usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener usuarios: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene todos los profesionales (barberos)
 */
function traer_profesionales(): array {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT ID_Profesional, Nombre, Especialidad, Telefono, Correo FROM profesional");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener profesionales: " . $e->getMessage());
        return [];
    }
}

/**
 * Registra un nuevo profesional (barbero)
 */
function registrar_profesional(string $nombre, string $correo, string $contrasena, string $especialidad, ?string $telefono = null): bool {
    try {
        $conn = getConnection();
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de correo inválido");
        }
        // Verificar que el correo no exista ya en la tabla profesional
        $stmt = $conn->prepare("SELECT ID_Profesional FROM profesional WHERE Correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            throw new Exception("El correo ya está registrado como profesional");
        }
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO profesional (Nombre, Correo, Contrasena, Especialidad, Telefono) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nombre, $correo, $hash, $especialidad, $telefono]);
    } catch (Exception $e) {
        error_log("Error al registrar profesional: " . $e->getMessage());
        return false;
    }
}

/**
 * Inicia sesión de un profesional
 */
function login_profesional(string $correo, string $contrasena): ?array {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT ID_Profesional, Nombre, Correo, Contrasena, Especialidad, Telefono FROM profesional WHERE Correo = ?");
        $stmt->execute([$correo]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prof) return null;
        if (!password_verify($contrasena, $prof['Contrasena'])) return null;
        unset($prof['Contrasena']);
        $_SESSION['profesional'] = $prof;
        return $prof;
    } catch (Exception $e) {
        error_log("Error al iniciar sesión profesional: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene todas las citas (opcionalmente por usuario)
 */
function traer_citas(?int $id_usuario = null): array {
    try {
        $conn = getConnection();
        $query = "SELECT c.ID_Cita, c.Fecha_hora, c.Estado, c.ID_Usuario, c.ID_Profesional FROM cita c";
        $params = [];
        if ($id_usuario !== null) {
            $query .= " WHERE c.ID_Usuario = ?";
            $params[] = $id_usuario;
        }
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener citas: " . $e->getMessage());
        return [];
    }
}

/**
 * Wrapper que devuelve la agenda de un profesional usando la función ya existente
 */
function traer_citas_profesional(int $id_profesional, bool $solo_pendientes = true): array {
    return obtener_agenda_barbero($id_profesional, $solo_pendientes);
}

/**
 * Actualiza el estado de una cita
 */
function actualizar_estado_cita(int $id_cita, string $nuevo_estado): bool {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE cita SET Estado = ? WHERE ID_Cita = ?");
        $stmt->execute([$nuevo_estado, $id_cita]);
        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error al actualizar estado de cita: " . $e->getMessage());
        return false;
    }
}

/**
 * Inserta una calificación para un profesional
 */
function insertar_calificacion(int $id_profesional, int $id_usuario, int $puntuacion, ?string $comentario = null): bool {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO calificacion (ID_Profesional, ID_Usuario, Puntuacion, Comentario, Fecha) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$id_profesional, $id_usuario, $puntuacion, $comentario]);
    } catch (PDOException $e) {
        error_log("Error al insertar calificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene las calificaciones de un profesional
 */
function traer_calificaciones_profesional(int $id_profesional): array {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT ID_Calificacion, ID_Usuario, Puntuacion, Comentario, Fecha FROM calificacion WHERE ID_Profesional = ? ORDER BY Fecha DESC");
        $stmt->execute([$id_profesional]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener calificaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Guarda disponibilidad de un profesional
 */
function guardar_disponibilidad(int $id_profesional, string $dia_semana, string $hora_inicial, string $hora_fin): bool {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO disponibilidad (ID_Profesional, Dia_semana, Hora_inicial, Hora_fin) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$id_profesional, $dia_semana, $hora_inicial, $hora_fin]);
    } catch (PDOException $e) {
        error_log("Error al guardar disponibilidad: " . $e->getMessage());
        return false;
    }
}

/**
 * Consulta disponibilidad de un profesional (opcional por día)
 */
function consultar_disponibilidad(int $id_profesional, ?string $dia_semana = null): array {
    try {
        $conn = getConnection();
        $query = "SELECT Dia_semana, Hora_inicial, Hora_fin FROM disponibilidad WHERE ID_Profesional = ?";
        $params = [$id_profesional];
        if ($dia_semana !== null) {
            $query .= " AND Dia_semana = ?";
            $params[] = $dia_semana;
        }
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al consultar disponibilidad: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene la lista de servicios disponibles
 */
function traer_servicios(): array {
    return obtener_servicios();
}

/* ==== FUNCIONES ADICIONALES FALTANTES ==== */

/**
 * Obtiene detalles de una cita específica por ID
 */
function traer_agenda_por_cita(int $id_cita): array {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("SELECT c.ID_Cita, c.Fecha_hora, c.Estado,
                                u.Nombre as Cliente, u.Telefono as Telefono_cliente,
                                p.Nombre as Profesional
               FROM cita c
               JOIN usuario u ON c.ID_Usuario = u.ID_Usuario
               JOIN profesional p ON c.ID_Profesional = p.ID_Profesional
               WHERE c.ID_Cita = ?");
        $stmt->execute([$id_cita]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cita) return [];

        // Obtener servicios de la cita
        $stmt2 = $conn->prepare("SELECT s.Nombre, dc.Precio_aplicado, s.ID_Servicio
                                 FROM detalle_cita dc
                                 JOIN servicio s ON dc.ID_Servicio = s.ID_Servicio
                                 WHERE dc.ID_Cita = ?");
        $stmt2->execute([$id_cita]);
        $cita['Servicios'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $cita;
    } catch (PDOException $e) {
        error_log("Error al obtener agenda por cita: " . $e->getMessage());
        return [];
    }
}

/**
 * Administrar servicios (CRUD)
 * - Si $id_servicio es null y $accion === 'actualizar', inserta un nuevo servicio y devuelve el ID generado.
 * - Si $id_servicio no es null y $accion === 'actualizar', actualiza el servicio y devuelve true.
 * - Si $accion === 'eliminar', borra el servicio y devuelve true.
 */
function administrar_servicios(?int $id_servicio, string $accion, array $datos = []): mixed {
    try {
        $conn = getConnection();

        switch ($accion) {
            case 'actualizar':
                $nombre  = $datos['Nombre'] ?? '';
                $desc    = $datos['Descripcion'] ?? '';
                $precio  = $datos['Precio'] ?? 0;
                $dur     = $datos['Duracion_min'] ?? 0;

                if ($id_servicio === null) {
                    // INSERTAR nuevo servicio
                    $stmt = $conn->prepare("
                        INSERT INTO servicio (Nombre, Descripcion, Precio, Duracion_min)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$nombre, $desc, $precio, $dur]);
                    return $conn->lastInsertId(); // devuelve el ID del nuevo registro
                } else {
                    // ACTUALIZAR servicio existente
                    $stmt = $conn->prepare("
                        UPDATE servicio
                        SET Nombre=?, Descripcion=?, Precio=?, Duracion_min=?
                        WHERE ID_Servicio=?
                    ");
                    return $stmt->execute([$nombre, $desc, $precio, $dur, $id_servicio]);
                }


            case 'eliminar':
                $stmt = $conn->prepare("DELETE FROM servicio WHERE ID_Servicio = ?");
                return $stmt->execute([$id_servicio]);

            default:
                error_log("Acción no válida en administrar_servicios: $accion");
                return false;
        }
    } catch (PDOException $e) {
        error_log("Error al administrar servicios: " . $e->getMessage());
        return false;
    }
}

/**
 * Notificar una cita (placeholder - no envía email real)
 */
function notificar_cita(int $id_cita, ?string $mensaje = null): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("SELECT Fecha_hora, Estado FROM cita WHERE ID_Cita = ?");
        $stmt->execute([$id_cita]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cita) return false;

        // Placeholder: registra la notificación en log
        $notificacion = $mensaje ?? "Cita programada para {$cita['Fecha_hora']}. Estado: {$cita['Estado']}.";
        error_log("NOTIFICACION CITA $id_cita: $notificacion");

        return true;
    } catch (Exception $e) {
        error_log("Error al notificar cita: " . $e->getMessage());
        return false;
    }
}

/**
 * Editar perfil de un usuario
 */
function editar_perfil(int $id_usuario, array $datos): bool {
    try {
        $conn = getConnection();

        $campos = [];
        $params = [];

        if (isset($datos['nombre'])) {
            $campos[] = "Nombre = ?";
            $params[] = $datos['nombre'];
        }
        if (isset($datos['correo'])) {
            $campos[] = "Correo = ?";
            $params[] = $datos['correo'];
        }
        if (isset($datos['telefono'])) {
            $campos[] = "Telefono = ?";
            $params[] = $datos['telefono'];
        }
        if (isset($datos['contrasena'])) {
            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $campos[] = "Contrasena = ?";
            $params[] = $hash;
        }

        if (empty($campos)) return false;

        $params[] = $id_usuario;
        $stmt = $conn->prepare("UPDATE usuario SET " . implode(", ", $campos) . " WHERE ID_Usuario = ?");
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Error al editar perfil: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar un usuario
 */
function eliminar_usuario(int $id_usuario): bool {
    try {
        $conn = getConnection();

        // Primero verificar que exista
        $stmt = $conn->prepare("SELECT ID_Usuario FROM usuario WHERE ID_Usuario = ?");
        $stmt->execute([$id_usuario]);
        if (!$stmt->fetch()) return false;

        // Eliminar usuario
        $stmt = $conn->prepare("DELETE FROM usuario WHERE ID_Usuario = ?");
        return $stmt->execute([$id_usuario]);
    } catch (PDOException $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        return false;
    }
}


