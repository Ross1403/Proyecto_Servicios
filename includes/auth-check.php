<?php
// ============================================================
// includes/auth-check.php
// Verifica que exista sesión activa. Si no, redirige al login.
// Incluir al inicio de CADA página del panel /admin/
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_rol'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /Proyecto_Servicios/admin/login.php');
    exit;
}

// Regenerar ID de sesión periódicamente para prevenir session fixation
if (empty($_SESSION['last_regen']) || (time() - $_SESSION['last_regen']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function get_csrf_token() {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        // Si es JSON AJAX
        if (empty($token) && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['csrf_token'] ?? '';
            // Restaurar para que otros scripts sigan leyendo de php://input si es necesario
            $_POST['csrf_token'] = $token; 
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                echo json_encode(['success' => false, 'mensaje' => 'Token CSRF inválido.']);
                exit;
            }
            die('Error CSRF: Solicitud no autorizada.');
        }
    }
}

// Para mayor seguridad en el panel, validar automáticamente todas las peticiones POST de las páginas incluidas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/admin/login.php') === false) {
    verify_csrf_token();
}
