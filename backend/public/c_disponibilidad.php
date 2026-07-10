<?php
/**
 * API JSON — Disponibilidad: obtener/guardar disponibilidad de un profesional
 * GET /backend/public/c_disponibilidad.php?id_profesional=X
 * POST /backend/public/c_disponibilidad.php con body: { id_profesional, disponibilidad: [{dia_semana, hora_inicial, hora_fin}, ...] }
 */

header('Content-Type: application/json; charset=utf-8');
$allowed_origins = ['http://localhost:5173', 'http://localhost:5174'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:5173');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener disponibilidad
    $id_profesional = $_GET['id_profesional'] ?? null;
    $dia_semana = $_GET['dia_semana'] ?? null;

    if (null === $id_profesional) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Parámetro id_profesional requerido']);
        exit;
    }

    try {
        $disponibilidad = consultar_disponibilidad((int)$id_profesional, $dia_semana);
        echo json_encode(['ok' => true, 'data' => $disponibilidad]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
        error_log('Error en c_disponibilidad.php (GET): ' . $e->getMessage());
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guardar disponibilidad
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
        exit;
    }

    $id_profesional = $input['id_profesional'] ?? null;
    $disponibilidad = $input['disponibilidad'] ?? [];

    if (null === $id_profesional || !is_array($disponibilidad) || empty($disponibilidad)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Parámetros requeridos: id_profesional y disponibilidad[]']);
        exit;
    }

    try {
        $todos_guardados = true;
        foreach ($disponibilidad as $item) {
            $dia_semana = $item['dia_semana'] ?? null;
            $hora_inicial = $item['hora_inicial'] ?? null;
            $hora_fin = $item['hora_fin'] ?? null;

            if (null === $dia_semana || null === $hora_inicial || null === $hora_fin) {
                continue;
            }

            $resultado = guardar_disponibilidad(
                (int)$id_profesional,
                (string)$dia_semana,
                (string)$hora_inicial,
                (string)$hora_fin
            );

            if (!$resultado) {
                $todos_guardados = false;
            }
        }

        if ($todos_guardados) {
            echo json_encode(['ok' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Error al guardar disponibilidad']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Error en el servidor']);
        error_log('Error en c_disponibilidad.php (POST): ' . $e->getMessage());
    }

} else {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
}
