<?php
/**
 * Backup y eliminación de profesionales con contraseñas en texto plano.
 * Ejecutar desde CLI: php delete_plain_profs.php
 */
require_once __DIR__ . '/app/bootstrap.php';

try {
    $conn = getConnection();
    // Seleccionar profesionales cuya contraseña NO parece hasheada
    $stmt = $conn->query("SELECT * FROM profesional");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $to_delete = [];
    foreach ($rows as $r) {
        $hash = $r['Contrasena'] ?? '';
        $len = strlen($hash);
        $is_hashed = false;
        if (preg_match('/^\\$(2y|2a|argon2i|argon2id)\\$/i', $hash)) $is_hashed = true;
        if (!$is_hashed && $len >= 50) $is_hashed = true;
        if (!$is_hashed) $to_delete[] = $r;
    }

    if (empty($to_delete)) {
        echo "No se encontraron profesionales con contraseñas en texto plano.\n";
        exit(0);
    }

    // Crear carpeta de backups
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    $ts = date('Ymd_His');
    $backupFile = $backupDir . "/profesionales_plain_{$ts}.json";
    file_put_contents($backupFile, json_encode($to_delete, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Preparar lista de IDs para borrar
    $ids = array_map(function($r){ return (int)$r['ID_Profesional']; }, $to_delete);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmtDel = $conn->prepare("DELETE FROM profesional WHERE ID_Profesional IN ($placeholders)");
    $stmtDel->execute($ids);

    echo "Backup guardado en: $backupFile\n";
    echo "Profesionales borrados (IDs): " . implode(', ', $ids) . "\n";
    exit(0);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
