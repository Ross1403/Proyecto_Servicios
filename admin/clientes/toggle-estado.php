<?php
// ============================================================
// admin/clientes/toggle-estado.php — AJAX toggle de estado/destacado
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'mensaje' => 'Petición inválida.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id    = filter_var($input['id']    ?? null, FILTER_VALIDATE_INT);
$table = $input['table'] ?? '';
$field = $input['field'] ?? '';
$value = filter_var($input['value'] ?? null, FILTER_VALIDATE_INT);

// Validar tabla y campo permitidos (whitelist)
$allowedTables = ['clientes', 'servicios', 'proyectos'];
$allowedFields = ['estado', 'destacado'];

if (!$id || !in_array($table, $allowedTables) || !in_array($field, $allowedFields) || !in_array($value, [0, 1])) {
    echo json_encode(['success' => false, 'mensaje' => 'Parámetros inválidos.']);
    exit;
}

$db = getDB();

try {
    // Columna PK dinámica
    $pkMap = ['clientes' => 'id_cliente', 'servicios' => 'id_servicio', 'proyectos' => 'id_proyecto'];
    $pk    = $pkMap[$table];

    $stmt = $db->prepare("UPDATE `{$table}` SET `{$field}` = :value WHERE `{$pk}` = :id");
    $stmt->execute([':value' => $value, ':id' => $id]);

    echo json_encode(['success' => true, 'mensaje' => 'Estado actualizado correctamente.']);
} catch (PDOException $e) {
    error_log('Toggle estado error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Error en la base de datos.']);
}
