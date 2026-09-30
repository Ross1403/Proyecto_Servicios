<?php
// admin/cotizaciones/eliminar.php
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM cotizaciones WHERE id_cotizacion = :id");
        $stmt->execute([':id' => $id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Cotización eliminada exitosamente.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al eliminar la cotización.'];
    }
}
header('Location: /Proyecto_Servicios/admin/cotizaciones/listar.php');
exit;
