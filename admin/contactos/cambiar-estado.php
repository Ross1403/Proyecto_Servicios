<?php
// ============================================================
// admin/contactos/cambiar-estado.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    
    $estados_validos = ['Nuevo', 'Revisado', 'Contactado', 'Cerrado'];
    
    if ($id && in_array($nuevo_estado, $estados_validos)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE contactos SET estado = :estado WHERE id_contacto = :id");
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Estado del mensaje actualizado.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'Error al actualizar el estado.'];
        }
    }
}

header('Location: /Proyecto_Servicios/admin/contactos/listar.php');
exit;
