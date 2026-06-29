<?php
require_once __DIR__ . '/../app/bootstrap.php';
$c = getConnection();

echo "=== profesional columns ===\n";
foreach ($c->query('SHOW COLUMNS FROM profesional')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['Field']} ({$r['Type']})\n";
}

echo "\n=== indexes on disponibilidad ===\n";
foreach ($c->query('SHOW INDEX FROM disponibilidad')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['Key_name']} -> {$r['Column_name']}\n";
}

echo "\n=== calificacion table ===\n";
$tables = $c->query('SHOW TABLES LIKE "calificacion"')->fetchAll();
echo empty($tables) ? "  not present\n" : "  present\n";
if ($tables) {
    foreach ($c->query('SHOW COLUMNS FROM calificacion')->fetchAll(PDO::FETCH_ASSOC) as $r) {
        echo "  {$r['Field']}\n";
    }
}
