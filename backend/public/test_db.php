<?php
require_once __DIR__ . '/../app/bootstrap.php';
try {
    $conn = getConnection();
    echo json_encode(["ok" => true, "message" => "Database connected"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}
