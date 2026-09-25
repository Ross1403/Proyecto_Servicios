<?php
// ============================================================
// admin/contactos/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM contactos WHERE id_contacto = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Mensaje eliminado correctamente.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al eliminar el mensaje.'];
    }
}

header('Location: /Proyecto_Servicios/admin/contactos/listar.php');
exit;
