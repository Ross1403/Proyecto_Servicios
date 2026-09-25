<?php
// ============================================================
// admin/proyectos/eliminar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = getDB();
        
        // Obtener la imagen para borrarla físicamente
        $stmtImg = $db->prepare("SELECT imagen FROM proyectos WHERE id_proyecto = :id");
        $stmtImg->execute([':id' => $id]);
        $proyecto = $stmtImg->fetch();
        
        // Eliminar registro
        $stmt = $db->prepare("DELETE FROM proyectos WHERE id_proyecto = :id");
        $stmt->execute([':id' => $id]);
        
        // Borrar archivo físico si existe
        if ($proyecto && $proyecto['imagen']) {
            $imgPath = __DIR__ . '/../../uploads/proyectos/' . $proyecto['imagen'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
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
