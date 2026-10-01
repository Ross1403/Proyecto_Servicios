<?php
// ============================================================
// admin/clientes/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    try {
        $db = getDB();
        
        // Obtener logo (solo para log o referencia, no eliminamos físicamente en Soft Delete)
        $stmtImg = $db->prepare("SELECT logo FROM clientes WHERE id_cliente = :id");
        $stmtImg->execute([':id' => $id]);
        $cliente = $stmtImg->fetch();
        
        // Eliminar registro de forma lógica (Soft Delete - Senior Pro)
        $stmt = $db->prepare("UPDATE clientes SET deleted_at = NOW() WHERE id_cliente = :id");
        $stmt->execute([':id' => $id]);
        
        // Ya no hacemos unlink() del logo físico, porque el registro podría restaurarse
        // if ($cliente && $cliente['logo']) { ... }
        
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Cliente eliminado correctamente.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error: El cliente tiene proyectos asociados. Elimínalos primero.'];
    }
}

header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
exit;
