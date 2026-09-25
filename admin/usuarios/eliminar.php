<?php
// ============================================================
// admin/usuarios/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['admin_rol'] !== 'admin') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'No tienes permisos para realizar esta acción.'];
    header('Location: /Proyecto_Servicios/admin/dashboard.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    if ($id === $_SESSION['admin_id']) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'No puedes eliminar tu propia cuenta de administrador.'];
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
            $stmt->execute([':id' => $id]);
            
            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Usuario eliminado correctamente.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al eliminar el usuario.'];
        }
    }
}

header('Location: /Proyecto_Servicios/admin/usuarios/listar.php');
exit;
