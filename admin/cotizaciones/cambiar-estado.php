<?php
// admin/cotizaciones/cambiar-estado.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $estado = $input['estado'] ?? '';
    
    // Fallback
    if(isset($input['value'])) $estado = $input['value'];

    $estadosPermitidos = ['Pendiente', 'Revisada', 'Contactado', 'Descartada'];

    if ($id && in_array($estado, $estadosPermitidos)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE cotizaciones SET estado = :estado WHERE id_cotizacion = :id");
            $stmt->execute([':estado' => $estado, ':id' => $id]);
            echo json_encode(['success' => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
            exit;
        }
    }
}
echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
