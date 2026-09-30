<?php
// guardar-cotizacion.php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $nombre = trim($input['nombre'] ?? '');
    $correo = trim($input['correo'] ?? '');
    $telefono = trim($input['telefono'] ?? '');
    $empresa = trim($input['empresa'] ?? '');
    $servicios = $input['servicios'] ?? [];
    $total = floatval($input['total'] ?? 0);

    if (empty($nombre) || empty($correo) || empty($empresa)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    $serviciosJson = json_encode($servicios, JSON_UNESCAPED_UNICODE);

    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO cotizaciones (nombre, correo, telefono, empresa, servicios_json, presupuesto_estimado) VALUES (:nombre, :correo, :telefono, :empresa, :servicios, :total)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':empresa' => $empresa,
            ':servicios' => $serviciosJson,
            ':total' => $total
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Error guardando cotizacion: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
