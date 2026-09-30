<?php
// api/v1/servicios.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/jwt.php';

// Validar Headers para JWT
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token JWT no proporcionado o formato inválido']);
    exit;
}

$token = $matches[1];
$userData = JWTAuth::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(['error' => 'Token JWT inválido o expirado']);
    exit;
}

// Token Válido - Procesar Endpoint
try {
    $db = getDB();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("SELECT id_servicio, nombre, descripcion, precio_base, estado FROM servicios");
        $servicios = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $servicios, 'user' => $userData]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no soportado en este endpoint de lectura']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno de base de datos']);
}
