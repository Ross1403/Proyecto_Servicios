<?php
// ============================================================
// admin/logout.php — Cierra sesión y redirige al login
// ============================================================
session_start();
session_unset();
session_destroy();

// Eliminar la cookie de sesión para mayor seguridad
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header('Location: /Proyecto_Servicios/admin/login.php?logout=1');
exit;
