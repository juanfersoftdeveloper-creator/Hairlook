<?php
/**
 * Apply incremental schema changes from database/hairlook.sql to live MySQL.
 * Run: php scripts/apply_schema_migration.php
 */
require_once __DIR__ . '/../app/bootstrap.php';

$steps = [
    [
        'name' => 'Add profesional.Rating column',
        'sql'  => "ALTER TABLE profesional
                   ADD COLUMN Rating decimal(3,2) NOT NULL DEFAULT 0.00
                   COMMENT 'Promedio de calificaciones, se recalcula automáticamente'",
        'skip_if' => function (PDO $c): bool {
            $cols = $c->query('SHOW COLUMNS FROM profesional')->fetchAll(PDO::FETCH_COLUMN);
            return in_array('Rating', $cols, true);
        },
    ],
    [
        'name' => 'Backfill Rating from calificacion (if table exists)',
        'sql'  => "UPDATE profesional p
                   SET Rating = COALESCE((
                       SELECT ROUND(AVG(Puntuacion), 2)
                       FROM calificacion c
                       WHERE c.ID_Profesional = p.ID_Profesional
                   ), 0.00)",
        'skip_if' => function (PDO $c): bool {
            $tables = $c->query("SHOW TABLES LIKE 'calificacion'")->fetchAll();
            if (empty($tables)) {
                return true;
            }
            $cols = $c->query('SHOW COLUMNS FROM profesional')->fetchAll(PDO::FETCH_COLUMN);
            return !in_array('Rating', $cols, true);
        },
    ],
    [
        'name' => 'Create notificacion table',
        'sql'  => "CREATE TABLE notificacion (
                       ID_Notificacion int(11) NOT NULL AUTO_INCREMENT,
                       ID_Cita int(11) NOT NULL,
                       ID_Profesional int(11) NOT NULL,
                       Mensaje text NOT NULL,
                       Leida tinyint(1) NOT NULL DEFAULT 0,
                       Fecha datetime NOT NULL,
                       PRIMARY KEY (ID_Notificacion),
                       KEY ID_Cita (ID_Cita),
                       KEY ID_Profesional (ID_Profesional),
                       CONSTRAINT notificacion_ibfk_1 FOREIGN KEY (ID_Cita) REFERENCES cita (ID_Cita),
                       CONSTRAINT notificacion_ibfk_2 FOREIGN KEY (ID_Profesional) REFERENCES profesional (ID_Profesional)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        'skip_if' => function (PDO $c): bool {
            $tables = $c->query("SHOW TABLES LIKE 'notificacion'")->fetchAll();
            return !empty($tables);
        },
    ],
    [
        'name' => 'Add uk_profesional_dia on disponibilidad',
        'sql'  => 'ALTER TABLE disponibilidad ADD UNIQUE KEY uk_profesional_dia (ID_Profesional, Dia_semana)',
        'skip_if' => function (PDO $c): bool {
            foreach ($c->query('SHOW INDEX FROM disponibilidad')->fetchAll(PDO::FETCH_ASSOC) as $idx) {
                if ($idx['Key_name'] === 'uk_profesional_dia') {
                    return true;
                }
            }
            return false;
        },
    ],
    [
        'name' => 'Add usuario profile columns (Apellidos, Cedula, Fecha_nacimiento, Direccion, Metodo_pago)',
        'sql'  => "ALTER TABLE usuario
                   ADD COLUMN Apellidos VARCHAR(100) NOT NULL DEFAULT '' AFTER Nombre,
                   ADD COLUMN Cedula VARCHAR(20) NULL AFTER Apellidos,
                   ADD COLUMN Fecha_nacimiento DATE NULL AFTER Cedula,
                   ADD COLUMN Direccion VARCHAR(255) NOT NULL DEFAULT '' AFTER Fecha_nacimiento,
                   ADD COLUMN Metodo_pago ENUM('Efectivo', 'Tarjeta', 'Digital') NOT NULL DEFAULT 'Efectivo' AFTER Direccion",
        'skip_if' => function (PDO $c): bool {
            $cols = $c->query('SHOW COLUMNS FROM usuario')->fetchAll(PDO::FETCH_COLUMN);
            return in_array('Apellidos', $cols, true);
        },
    ],
    [
        'name' => 'Add uk_usuario_cedula on usuario',
        'sql'  => 'ALTER TABLE usuario ADD UNIQUE KEY uk_usuario_cedula (Cedula)',
        'skip_if' => function (PDO $c): bool {
            foreach ($c->query('SHOW INDEX FROM usuario')->fetchAll(PDO::FETCH_ASSOC) as $idx) {
                if ($idx['Key_name'] === 'uk_usuario_cedula') {
                    return true;
                }
            }
            return false;
        },
    ],
];

echo "HairLook — Applying schema migration\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $conn = getConnection();

    foreach ($steps as $step) {
        echo "→ {$step['name']}... ";

        if ($step['skip_if']($conn)) {
            echo "skipped (already applied or not needed)\n";
            continue;
        }

        try {
            $conn->exec($step['sql']);
            echo "OK\n";
        } catch (PDOException $e) {
            if ($step['name'] === 'Add uk_profesional_dia on disponibilidad') {
                echo "FAILED\n";
                echo "  Duplicate (ID_Profesional, Dia_semana) rows may exist. Clean them and re-run.\n";
                echo "  Error: {$e->getMessage()}\n";
            } else {
                throw $e;
            }
        }
    }

    echo "\n" . str_repeat('=', 50) . "\n";
    echo "Migration finished. Running schema check...\n\n";
    passthru('php ' . escapeshellarg(__DIR__ . '/check_schema.php'));
} catch (Throwable $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}
