<?php
// ============================================================
// admin/clientes/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = getDB();
        
        // Obtener logo para eliminar archivo
        $stmtImg = $db->prepare("SELECT logo FROM clientes WHERE id_cliente = :id");
        $stmtImg->execute([':id' => $id]);
        $cliente = $stmtImg->fetch();
        
        // Eliminar registro
        $stmt = $db->prepare("DELETE FROM clientes WHERE id_cliente = :id");
        $stmt->execute([':id' => $id]);
        
        if ($cliente && $cliente['logo']) {
            $imgPath = __DIR__ . '/../../uploads/clientes/' . $cliente['logo'];
            if (file_exists($imgPath)) unlink($imgPath);
        }
        
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Cliente eliminado correctamente.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error: El cliente tiene proyectos asociados. Elimínalos primero.'];
    }
}

header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
exit;
