<?php
// includes/logger.php

class Logger {
    public static function log($accion, $modulo, $detalles = '') {
        try {
            $db = getDB();
            $id_usuario = $_SESSION['admin_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            // Si el array de detalles se pasa como parámetro, lo convertimos a JSON
            if (is_array($detalles)) {
                $detalles = json_encode($detalles, JSON_UNESCAPED_UNICODE);
            }

            $stmt = $db->prepare("
                INSERT INTO audit_logs (id_usuario, accion, modulo, detalles, ip)
                VALUES (:id_usuario, :accion, :modulo, :detalles, :ip)
            ");
            
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':accion'     => $accion,
                ':modulo'     => $modulo,
                ':detalles'   => $detalles,
                ':ip'         => $ip
            ]);
        } catch (PDOException $e) {
            // Un fallo en el log no debería detener la ejecución del script principal,
            // pero podríamos enviarlo al log de errores del sistema.
            error_log("Error en Logger de Auditoría: " . $e->getMessage());
        }
    }
}
