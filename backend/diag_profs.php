<?php
// Script diagnóstico: lista profesionales y verifica si la contraseña parece hasheada
require_once __DIR__ . '/app/bootstrap.php';

try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT ID_Profesional, Nombre, Correo, Contrasena FROM profesional LIMIT 200");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        $hash = $r['Contrasena'] ?? '';
        $len = strlen($hash);
        $is_hashed = false;
        if (preg_match('/^\$(2y|2a|argon2i|argon2id)\$/i', $hash)) $is_hashed = true;
        // Fallback: bcrypt hashes typically have length 60
        if (!$is_hashed && $len >= 50) $is_hashed = true;
        $out[] = [
            'ID_Profesional' => $r['ID_Profesional'],
            'Nombre' => $r['Nombre'],
            'Correo' => $r['Correo'],
            'Contrasena_len' => $len,
            'Contrasena_preview' => substr($hash,0,6),
            'looks_hashed' => $is_hashed
        ];
    }

    echo json_encode(['ok' => true, 'count' => count($out), 'rows' => $out], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
