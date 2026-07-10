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
        $db = require __DIR__ . '/../config/database.php';
        $host = $db['host'];
        $dbname = $db['dbname'];
        $username = $db['username'];
        $password = $db['password'];
        $charset = $db['charset'] ?? 'utf8mb4';

        $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        throw new Exception("No se pudo conectar a la base de datos");
    }
}

// === FUNCIONES DE AUTENTICACIÓN ===

/**
 * Registra un nuevo usuario con todos los campos del formulario.
 * Valida que las contraseñas coincidan antes de hashear.
 */
function registrar_usuario(
    string $nombre,
    string $apellidos,
    string $cedula,
    string $fecha_nacimiento,
    string $direccion,
    string $correo,
    string $contrasena,
    string $confirmar_contrasena,
    string $metodo_pago
): bool {
    try {
        $conn = getConnection();

        // Validar campos obligatorios
        $nombre           = trim($nombre);
        $apellidos        = trim($apellidos);
        $cedula           = trim($cedula);
        $direccion        = trim($direccion);
        $correo           = trim($correo);
        $metodo_pago      = trim($metodo_pago);

        if (
            $nombre === '' ||
            $apellidos === '' ||
            $cedula === '' ||
            $fecha_nacimiento === '' ||
            $direccion === '' ||
            $correo === '' ||
            $contrasena === '' ||
            $confirmar_contrasena === '' ||
            $metodo_pago === ''
        ) {
            throw new Exception('Todos los campos son obligatorios');
        }

        // Validar correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de correo inválido');
        }

        // Validar que las contraseñas coincidan ANTES de hashear
        if ($contrasena !== $confirmar_contrasena) {
            throw new Exception('Las contraseñas no coinciden');
        }

        if (strlen($contrasena) < 4) {
            throw new Exception('La contraseña debe tener al menos 4 caracteres');
        }

        // Validar fecha de nacimiento (formato YYYY-MM-DD)
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha_nacimiento) {
            throw new Exception('Fecha de nacimiento inválida');
        }

        // Normalizar método de pago (frontend envía: efectivo, tarjeta, digital)
        $metodosPermitidos = [
            'efectivo' => 'Efectivo',
            'tarjeta'  => 'Tarjeta',
            'digital'  => 'Digital',
            'Efectivo' => 'Efectivo',
            'Tarjeta'  => 'Tarjeta',
            'Digital'  => 'Digital',
        ];

        $metodoNormalizado = $metodosPermitidos[strtolower($metodo_pago)] ?? null;
        if ($metodoNormalizado === null) {
            throw new Exception('Método de pago no válido');
        }

        // Verificar correo único
        $stmt = $conn->prepare('SELECT ID_Usuario FROM usuario WHERE Correo = ?');
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            throw new Exception('El correo ya está registrado');
        }

        // Verificar cédula única
        $stmt = $conn->prepare('SELECT ID_Usuario FROM usuario WHERE Cedula = ?');
        $stmt->execute([$cedula]);
        if ($stmt->fetch()) {
            throw new Exception('La cédula ya está registrada');
        }

        // Hashear contraseña solo después de validar coincidencia
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('
            INSERT INTO usuario (
                Nombre,
                Apellidos,
                Cedula,
                Fecha_nacimiento,
                Direccion,
                Metodo_pago,
                Correo,
                Contrasena,
                Fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');

        return $stmt->execute([
            $nombre,
            $apellidos,
            $cedula,
            $fecha_nacimiento,
            $direccion,
            $metodoNormalizado,
            $correo,
            $hash,
        ]);
    } catch (PDOException $e) {
        error_log('Error al registrar usuario: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log('Error: ' . $e->getMessage());
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
    // Dado que la tabla no contiene columna 'Rol', simplemente verifica que haya una sesión activa.
    session_start();
    return isset($_SESSION['usuario']);
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
 * Crea una nueva cita en la base de datos.
 *
 * IMPORTANTE: toda cita nueva entra como 'pendiente', sin importar
 * la modalidad (local o domicilio). Esto es necesario para que el
 * profesional pueda aceptarla o rechazarla (ver HU06 - notificaciones).
 * Antes este valor se ponía en 'confirmada' automáticamente para citas
 * en local, lo cual saltaba ese paso de aprobación.
 */
function crear_cita(int $id_usuario, int $id_profesional, string $fecha, string $hora, string $tipo = 'local'): ?int {
    try {
        $conn = getConnection();

        $fecha_hora = $fecha . ' ' . $hora;
        $estado = 'pendiente';

        $stmt = $conn->prepare("
            INSERT INTO cita (Fecha_hora, Estado, ID_Usuario, ID_Profesional)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$fecha_hora, $estado, $id_usuario, $id_profesional]);
        $id_cita = (int) $conn->lastInsertId();

        // Generar notificación para el profesional (ver fix de notificar_cita)
        notificar_cita($id_cita, "Nueva solicitud de cita para el {$fecha_hora}.");

        return $id_cita;
    } catch (PDOException $e) {
        error_log("Error al crear cita: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene las citas de un profesional.
 *
 * Antes esta función hacía 1 query para traer las citas y luego
 * 1 query adicional POR CADA CITA para traer sus servicios (problema N+1).
 * Con 50 citas eso son 51 queries. Ahora se trae todo en una sola
 * consulta con JOIN + GROUP_CONCAT y se reconstruye el arreglo en PHP.
 *
 * También se corrige "Teléfono" (con tilde) que no coincidía con el
 * nombre real de la columna "Telefono" en la base de datos.
 */
function obtener_agenda_barbero(int $id_profesional, bool $solo_pendientes = true): array {
    try {
        $conn = getConnection();

        $query = "
            SELECT c.ID_Cita, c.Fecha_hora, c.Estado,
                   u.Nombre as Cliente, u.Telefono as Telefono_cliente,
                   u.ID_Usuario,
                   GROUP_CONCAT(s.Nombre SEPARATOR '||')           AS Nombres_servicios,
                   GROUP_CONCAT(dc.Precio_aplicado SEPARATOR '||') AS Precios_servicios
            FROM cita c
            JOIN usuario u ON c.ID_Usuario = u.ID_Usuario
            LEFT JOIN detalle_cita dc ON dc.ID_Cita = c.ID_Cita
            LEFT JOIN servicio s      ON s.ID_Servicio = dc.ID_Servicio
            WHERE c.ID_Profesional = ?
        ";

        $params = [$id_profesional];

        if ($solo_pendientes) {
            $query .= " AND c.Estado IN ('pendiente', 'confirmada')";
        }

        $query .= " GROUP BY c.ID_Cita ORDER BY c.Fecha_hora ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Reconstruir el arreglo de servicios a partir de los strings concatenados
        foreach ($citas as &$cita) {
            $cita['Servicios'] = [];

            if (!empty($cita['Nombres_servicios'])) {
                $nombres = explode('||', $cita['Nombres_servicios']);
                $precios = explode('||', $cita['Precios_servicios']);

                foreach ($nombres as $i => $nombre) {
                    $cita['Servicios'][] = [
                        'Nombre'          => $nombre,
                        'Precio_aplicado' => $precios[$i] ?? null,
                    ];
                }
            }

            unset($cita['Nombres_servicios'], $cita['Precios_servicios']);
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
// session_start() se controla desde cada endpoint público para evitar llamadas múltiples
// Si se desea habilitar globalmente, mover session_start() al inicio de los controladores públicos.

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
 * Obtiene todos los profesionales (barberos).
 * Se agregó Rating al SELECT para que la pantalla de "Peluqueros
 * cercanos" (HU02) pueda mostrar la calificación sin otra consulta.
 */
function traer_profesionales(): array {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT ID_Profesional, Nombre, Especialidad, Telefono, Correo, Rating FROM profesional");
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
 * Mejorado para incluir información del profesional y servicios
 */
function traer_citas(?int $id_usuario = null): array {
    try {
        $conn = getConnection();
        $query = "
            SELECT c.ID_Cita, c.Fecha_hora AS Fecha, 
                   CASE WHEN c.Fecha_hora IS NOT NULL THEN DATE_FORMAT(c.Fecha_hora, '%H:%i') ELSE NULL END AS Hora,
                   c.Estado, c.ID_Usuario, c.ID_Profesional,
                   p.Nombre AS NombreProfesional, p.Especialidad,
                   GROUP_CONCAT(s.Nombre SEPARATOR ', ') AS NombreServicio,
                   COALESCE(GROUP_CONCAT(dc.Precio_aplicado), 0) AS Precio
            FROM cita c
            JOIN profesional p ON c.ID_Profesional = p.ID_Profesional
            LEFT JOIN detalle_cita dc ON dc.ID_Cita = c.ID_Cita
            LEFT JOIN servicio s ON s.ID_Servicio = dc.ID_Servicio
        ";
        $params = [];
        if ($id_usuario !== null) {
            $query .= " WHERE c.ID_Usuario = ?";
            $params[] = $id_usuario;
        }
        $query .= " GROUP BY c.ID_Cita ORDER BY c.Fecha_hora DESC";
        
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
 * Inserta una calificación para un profesional (HU04).
 *
 * Antes esta función solo guardaba la fila en `calificacion` pero
 * nunca tocaba el rating del profesional — no existía ni la columna.
 * Ahora, después de guardar, recalcula el promedio con AVG() y
 * actualiza `profesional.Rating`, que es lo que se muestra en el
 * perfil público y en la búsqueda de profesionales (HU02).
 */
function insertar_calificacion(int $id_profesional, int $id_usuario, int $puntuacion, ?string $comentario = null): bool {
    try {
        $conn = getConnection();

        // Validar rango antes de guardar (1 a 5)
        if ($puntuacion < 1 || $puntuacion > 5) {
            error_log("Puntuación fuera de rango al calificar: $puntuacion");
            return false;
        }

        $stmt = $conn->prepare("
            INSERT INTO calificacion (ID_Profesional, ID_Usuario, Puntuacion, Comentario, Fecha)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insertado = $stmt->execute([$id_profesional, $id_usuario, $puntuacion, $comentario]);

        if ($insertado) {
            actualizar_rating_profesional($id_profesional);
        }

        return $insertado;
    } catch (PDOException $e) {
        error_log("Error al insertar calificación: " . $e->getMessage());
        return false;
    }
}

/**
 * Recalcula el rating promedio de un profesional a partir de
 * todas sus calificaciones y lo guarda en `profesional.Rating`.
 * Función nueva: sin esto, el rating quedaba siempre en 0.00.
 */
function actualizar_rating_profesional(int $id_profesional): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            UPDATE profesional
            SET Rating = (
                SELECT ROUND(AVG(Puntuacion), 2)
                FROM calificacion
                WHERE ID_Profesional = ?
            )
            WHERE ID_Profesional = ?
        ");

        return $stmt->execute([$id_profesional, $id_profesional]);
    } catch (PDOException $e) {
        error_log("Error al actualizar rating: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene las calificaciones de un profesional.
 *
 * Se agregó un JOIN con `usuario` para traer el nombre del cliente
 * que calificó. Antes solo se devolvía ID_Usuario, lo cual obligaba
 * al frontend a hacer una consulta extra por cada review para mostrar
 * el nombre (otro mini problema N+1).
 */
function traer_calificaciones_profesional(int $id_profesional): array {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            SELECT cal.ID_Calificacion, cal.Puntuacion, cal.Comentario, cal.Fecha,
                   u.Nombre AS Nombre_cliente
            FROM calificacion cal
            JOIN usuario u ON cal.ID_Usuario = u.ID_Usuario
            WHERE cal.ID_Profesional = ?
            ORDER BY cal.Fecha DESC
        ");
        $stmt->execute([$id_profesional]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener calificaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Guarda (o actualiza) la disponibilidad de un profesional para un día.
 *
 * Antes esta función SIEMPRE insertaba una fila nueva, así que si el
 * profesional guardaba su horario del Lunes dos veces, terminaba con
 * dos filas distintas para el mismo día (datos duplicados/contradictorios).
 * Ahora usa ON DUPLICATE KEY UPDATE: si ya existe una fila para ese
 * profesional+día (gracias al índice único uk_profesional_dia en la BD),
 * actualiza las horas en vez de crear una fila nueva.
 */
function guardar_disponibilidad(int $id_profesional, string $dia_semana, string $hora_inicial, string $hora_fin): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            INSERT INTO disponibilidad (ID_Profesional, Dia_semana, Hora_inicial, Hora_fin)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                Hora_inicial = VALUES(Hora_inicial),
                Hora_fin     = VALUES(Hora_fin)
        ");

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
                                u.Nombre as Cliente, u.Teléfono as Telefono_cliente,
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
 * Crea un nuevo servicio en el catálogo.
 * Antes esto vivía mezclado dentro de administrar_servicios() devolviendo
 * un tipo "mixed" (a veces int, a veces bool), lo que obligaba a quien
 * llamara la función a adivinar qué tipo de dato le iba a llegar.
 * Separarlo deja un contrato claro: esta función SIEMPRE devuelve el
 * ID del nuevo servicio, o null si falló.
 */
function crear_servicio(string $nombre, string $descripcion, float $precio, int $duracion_min): ?int {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            INSERT INTO servicio (Nombre, Descripcion, Precio, Duracion_min)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$nombre, $descripcion, $precio, $duracion_min]);

        return (int) $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error al crear servicio: " . $e->getMessage());
        return null;
    }
}

/**
 * Actualiza un servicio existente del catálogo.
 * Siempre devuelve bool, sin mezclarse con la lógica de creación.
 */
function actualizar_servicio(int $id_servicio, string $nombre, string $descripcion, float $precio, int $duracion_min): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("
            UPDATE servicio
            SET Nombre = ?, Descripcion = ?, Precio = ?, Duracion_min = ?
            WHERE ID_Servicio = ?
        ");

        $stmt->execute([$nombre, $descripcion, $precio, $duracion_min, $id_servicio]);
        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error al actualizar servicio: " . $e->getMessage());
        return false;
    }
}

/**
 * Elimina un servicio del catálogo.
 */
function eliminar_servicio(int $id_servicio): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("DELETE FROM servicio WHERE ID_Servicio = ?");
        $stmt->execute([$id_servicio]);

        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error al eliminar servicio: " . $e->getMessage());
        return false;
    }
}

/**
 * Notifica al profesional sobre una cita (HU06).
 *
 * Antes esta función solo hacía error_log(), es decir, escribía en
 * el archivo de logs del servidor pero no guardaba nada que la app
 * pudiera mostrar. Como resultado, la pantalla de notificaciones no
 * tenía de dónde leer datos. Ahora se inserta en la tabla `notificacion`
 * (nueva en el SQL) para que sí exista un registro consultable.
 */
function notificar_cita(int $id_cita, ?string $mensaje = null): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("SELECT Fecha_hora, Estado, ID_Profesional FROM cita WHERE ID_Cita = ?");
        $stmt->execute([$id_cita]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cita) return false;

        $texto = $mensaje ?? "Cita programada para {$cita['Fecha_hora']}. Estado: {$cita['Estado']}.";

        $stmt2 = $conn->prepare("
            INSERT INTO notificacion (ID_Cita, ID_Profesional, Mensaje, Leida, Fecha)
            VALUES (?, ?, ?, 0, NOW())
        ");

        return $stmt2->execute([$id_cita, $cita['ID_Profesional'], $texto]);
    } catch (PDOException $e) {
        error_log("Error al notificar cita: " . $e->getMessage());
        return false;
    }
}

/**
 * Trae las notificaciones de un profesional (las más recientes primero).
 * Función nueva: complementa a notificar_cita() para poder LEER
 * lo que se guarda, que es lo que la pantalla de notificaciones necesita.
 */
function traer_notificaciones(int $id_profesional, bool $solo_no_leidas = false): array {
    try {
        $conn = getConnection();

        $query = "SELECT ID_Notificacion, ID_Cita, Mensaje, Leida, Fecha
                  FROM notificacion
                  WHERE ID_Profesional = ?";

        if ($solo_no_leidas) {
            $query .= " AND Leida = 0";
        }

        $query .= " ORDER BY Fecha DESC";

        $stmt = $conn->prepare($query);
        $stmt->execute([$id_profesional]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al traer notificaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Marca una notificación como leída.
 * Función nueva: necesaria para que el badge de "no leídas" baje
 * cuando el profesional abre la notificación.
 */
function marcar_notificacion_leida(int $id_notificacion): bool {
    try {
        $conn = getConnection();

        $stmt = $conn->prepare("UPDATE notificacion SET Leida = 1 WHERE ID_Notificacion = ?");
        $stmt->execute([$id_notificacion]);

        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        error_log("Error al marcar notificación: " . $e->getMessage());
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
