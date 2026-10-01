<?php
// ============================================================
// admin/logout.php — Cierra sesión y redirige al login
// ============================================================
session_start();
if (!empty($_SESSION['admin_id'])) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/logger.php';
    Logger::log('Cierre de sesión', 'Autenticación');
}
session_unset();
session_destroy();

// Eliminar la cookie de sesión para mayor seguridad
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header('Location: /Proyecto_Servicios/admin/login.php?logout=1');
exit;
