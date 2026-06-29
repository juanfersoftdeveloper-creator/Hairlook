<?php
/**
 * Compare live MySQL schema vs database/hairlook.sql expectations.
 * Run: php scripts/check_schema.php
 */
require_once __DIR__ . '/../app/bootstrap.php';

$expectedTables = [
    'cita', 'calificacion', 'detalle_cita', 'disponibilidad', 'imagen_referencia',
    'profesional', 'servicio', 'usuario', 'notificacion',
];

$expectedColumns = [
    'usuario' => [
        'ID_Usuario', 'Nombre', 'Apellidos', 'Cedula', 'Fecha_nacimiento', 'Direccion',
        'Metodo_pago', 'Correo', 'Contrasena', 'Telefono', 'Foto_perfil', 'Fecha_registro',
    ],
    'servicio' => ['ID_Servicio', 'Nombre', 'Descripcion', 'Precio', 'Duracion_min'],
    'profesional' => ['ID_Profesional', 'Nombre', 'Especialidad', 'Telefono', 'Correo', 'Contrasena', 'Foto', 'Rating'],
    'calificacion' => ['ID_Calificacion', 'ID_Profesional', 'ID_Usuario', 'Puntuacion', 'Comentario', 'Fecha'],
    'notificacion' => ['ID_Notificacion', 'ID_Cita', 'ID_Profesional', 'Mensaje', 'Leida', 'Fecha'],
    'disponibilidad' => ['ID_Disponibilidad', 'Dia_semana', 'Hora_inicial', 'Hora_fin', 'ID_Profesional'],
];

echo "HairLook — Schema sync check\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $conn = getConnection();

    $stmt = $conn->query("SHOW TABLES");
    $liveTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "TABLES\n";
    echo str_repeat('-', 50) . "\n";
    foreach ($expectedTables as $table) {
        $exists = in_array($table, $liveTables, true);
        echo sprintf("  %-22s %s\n", $table, $exists ? '[OK]' : '[MISSING]');
    }

    $extra = array_diff($liveTables, $expectedTables);
    if ($extra) {
        echo "\n  Extra tables in MySQL (not in hairlook.sql):\n";
        foreach ($extra as $t) {
            echo "    - $t\n";
        }
    }

    echo "\nCOLUMNS (key tables)\n";
    echo str_repeat('-', 50) . "\n";
    $issues = [];

    foreach ($expectedColumns as $table => $cols) {
        if (!in_array($table, $liveTables, true)) {
            $issues[] = "Table `$table` missing entirely";
            continue;
        }

        $stmt = $conn->query("SHOW COLUMNS FROM `$table`");
        $liveCols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

        echo "\n  $table:\n";
        foreach ($cols as $col) {
            $ok = in_array($col, $liveCols, true);
            echo sprintf("    %-20s %s\n", $col, $ok ? '[OK]' : '[MISSING]');
            if (!$ok) {
                $issues[] = "Column `$table`.`$col` missing in MySQL";
            }
        }

        $unexpected = array_diff($liveCols, $cols);
        foreach ($unexpected as $col) {
            echo sprintf("    %-20s [EXTRA in DB]\n", $col);
            $issues[] = "Column `$table`.`$col` exists in MySQL but not in hairlook.sql";
        }
    }

    echo "\n" . str_repeat('=', 50) . "\n";
    if (empty($issues)) {
        echo "RESULT: MySQL matches database/hairlook.sql\n";
    } else {
        echo "RESULT: MySQL is OUT OF SYNC with database/hairlook.sql\n\n";
        echo "Issues found:\n";
        foreach ($issues as $i => $issue) {
            echo "  " . ($i + 1) . ". $issue\n";
        }
        echo "\nTo apply the SQL file, re-import in phpMyAdmin or run:\n";
        echo "  mysql -u root hairlook < database/hairlook.sql\n";
        echo "(Warning: full import may fail if tables already exist — use migrations or drop DB first.)\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
