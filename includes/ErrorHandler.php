<?php
// ============================================================
// includes/ErrorHandler.php — Error Tracking (Senior Pro)
// Atrapa errores no controlados para que el cliente no los vea,
// y los guarda en un archivo de log para el administrador.
// ============================================================

class ErrorHandler {
    
    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file = '', $line = 0) {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException(Throwable $exception) {
        $logMessage = sprintf(
            "[%s] ERROR: %s en %s (Línea %d) | Stack: %s\n",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            str_replace("\n", " ", $exception->getTraceAsString())
        );

        // Guardar silenciosamente en log local
        $logFile = __DIR__ . '/../system_errors.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        // Si es una petición AJAX (XMLHttpRequest), devolver JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Ocurrió un error interno. Intenta nuevamente más tarde.']);
            exit;
        }

        // Si no es AJAX, y no estamos en entorno local, mostrar vista de error bonita
        // Como estamos en XAMPP (desarrollo), mostramos un mensaje sutil
        echo '<div style="background:#1c2128;color:#FF4D6A;padding:20px;font-family:sans-serif;text-align:center;border-top:4px solid #FF4D6A;">
                <h3>Ups, algo salió mal.</h3>
                <p>Nuestro equipo ya fue notificado. Por favor, regresa a la página principal.</p>
                <!-- Solo para dev local: ' . htmlspecialchars($exception->getMessage()) . ' -->
              </div>';
        exit;
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleException(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }
}

// Inicializar automáticamente el manejador
ErrorHandler::register();
