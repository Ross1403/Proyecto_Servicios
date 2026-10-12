<?php
// ============================================================
// admin/proyectos/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    try {
        $db = getDB();
        
        // Obtener la imagen y zip para borrarlos físicamente
        $stmtArchivos = $db->prepare("SELECT imagen, archivo_zip FROM proyectos WHERE id_proyecto = :id");
        $stmtArchivos->execute([':id' => $id]);
        $proyecto = $stmtArchivos->fetch();
        
        // Eliminar registro
        $stmt = $db->prepare("DELETE FROM proyectos WHERE id_proyecto = :id");
        $stmt->execute([':id' => $id]);
        
        // Borrar imagen si existe
        if ($proyecto && $proyecto['imagen']) {
            $imgPath = __DIR__ . '/../../uploads/proyectos/' . $proyecto['imagen'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

        // Borrar entregable ZIP si existe
        if ($proyecto && $proyecto['archivo_zip']) {
            $zipPath = __DIR__ . '/../../uploads/proyectos/entregables/' . $proyecto['archivo_zip'];
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        }
        
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Proyecto eliminado correctamente.'];
    } catch (PDOException $e) {
        error_log("Error eliminando proyecto: " . $e->getMessage());
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al eliminar el proyecto. Asegúrate de que no tenga datos vinculados.'];
    }
}

header('Location: /Proyecto_Servicios/admin/proyectos/listar.php');
exit;
