<?php
// api/v1/sse-notificaciones.php
// IMPORTANTE: Para SSE, el script se queda corriendo
// Solo es seguro si los limites de tiempo de PHP lo permiten y session_write_close se llama.

// Deshabilitar caché y establecer cabeceras SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('X-Accel-Buffering: no'); // Para Nginx si aplicara

// Desconectar la sesión inmediatamente para que no bloquee otros scripts del admin
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Si no está logueado como admin, cortamos el SSE
    echo "data: " . json_encode(['error' => 'No autorizado']) . "\n\n";
    flush();
    exit;
}
session_write_close();

require_once __DIR__ . '/../../config/database.php';
$db = getDB();

// Loop infinito para empujar notificaciones
while (true) {
    // Si el cliente aborta la conexión, salimos del bucle
    if (connection_aborted()) {
        break;
    }

    try {
        // Buscar notificaciones no leídas
        $stmt = $db->query("SELECT id_notificacion, tipo, mensaje, url, fecha FROM notificaciones WHERE leido = 0 ORDER BY fecha ASC");
        $notificaciones = $stmt->fetchAll();

        if (count($notificaciones) > 0) {
            // Empujamos las notificaciones al cliente en tiempo real
            echo "data: " . json_encode(['notificaciones' => $notificaciones]) . "\n\n";
            ob_flush();
            flush();

            // Marcarlas como leídas para no enviarlas otra vez
            $ids = array_column($notificaciones, 'id_notificacion');
            $in  = str_repeat('?,', count($ids) - 1) . '?';
            $update = $db->prepare("UPDATE notificaciones SET leido = 1 WHERE id_notificacion IN ($in)");
            $update->execute($ids);
        }
    } catch (PDOException $e) {
        // Ignoramos errores de DB para no romper el bucle por un fallo temporal
    }

    // Esperar 5 segundos antes de consultar nuevamente
    sleep(5);
}
