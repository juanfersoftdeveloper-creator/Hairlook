<?php
require 'Funciones Hairlook/funciones_barberia.php';
//tablas disponibles: usuario, servicio, profesional, disponibilidad, detalle_cita, cita, 
//imagen_referencia, calificacion.

// Este script muestra las columnas de una tabla dada por el usuario. Es útil para verificar 
//la estructura de la base de datos y asegurarse de que las funciones estén alineadas con 
//los nombres de las columnas.
try {
    $tabla = trim(readline("Ingrese el nombre de la tabla: "));
    $c = getConnection();
    $stmt = $c->query("SHOW COLUMNS FROM $tabla");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cols, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}


//traer_citas(?int $id_usuario = null)
echo "========== listando las citas existentes ===========\n";
//echo json_encode(traer_citas(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

//echo json_encode(traer_agenda_por_cita("7"), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
