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
    // Guardar URL de destino para redirigir después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME'], 2), '/') . '/../admin/login.php');
    exit;
}

// Regenerar ID de sesión periódicamente para prevenir session fixation
if (empty($_SESSION['last_regen']) || (time() - $_SESSION['last_regen']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regen'] = time();
}
