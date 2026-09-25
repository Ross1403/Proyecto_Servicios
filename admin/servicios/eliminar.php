<?php
// ============================================================
// admin/servicios/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM servicios WHERE id_servicio = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Servicio eliminado correctamente.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al eliminar el servicio. Asegúrate de que no esté vinculado.'];
    }
}

header('Location: /Proyecto_Servicios/admin/servicios/listar.php');
exit;
