<?php
// api/v1/auth.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// ============================================================
// RATE LIMITING - Prevención de fuerza bruta (Security Pro)
// ============================================================
session_start();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'login_attempts_' . md5($ip);

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'locked_until' => 0];
}

if (time() < $_SESSION[$rateLimitKey]['locked_until']) {
    http_response_code(429); // Too Many Requests
    $remaining = ceil(($_SESSION[$rateLimitKey]['locked_until'] - time()) / 60);
    echo json_encode(['error' => "Demasiados intentos fallidos. Intente nuevamente en $remaining minuto(s)."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y password requeridos']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id_usuario, nombre, email, password, rol FROM usuarios WHERE email = :email AND estado = 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Resetear rate limiting al tener éxito
        $_SESSION[$rateLimitKey] = ['count' => 0, 'locked_until' => 0];

        // Generar JWT
        $payload = [
            'id_usuario' => $user['id_usuario'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'nombre' => $user['nombre']
        ];
        $token = JWTAuth::generateToken($payload);
        
        echo json_encode([
            'success' => true,
            'message' => 'Autenticación exitosa',
            'token' => $token,
            'user' => [
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ]
        ]);
    } else {
        // Aumentar contador de fallos
        $_SESSION[$rateLimitKey]['count']++;
        
        // Si falla 5 veces, bloquear por 15 minutos
        if ($_SESSION[$rateLimitKey]['count'] >= 5) {
            $_SESSION[$rateLimitKey]['locked_until'] = time() + (15 * 60);
        }

        http_response_code(401);
        $intentos_restantes = 5 - $_SESSION[$rateLimitKey]['count'];
        echo json_encode([
            'error' => 'Credenciales inválidas.', 
            'intentos_restantes' => max(0, $intentos_restantes)
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
