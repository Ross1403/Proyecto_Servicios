<?php
// ============================================================
// config/database.php — Conexión PDO para Devioz Proyectos
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'devioz_proyectos');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP por defecto no tiene password
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna una instancia PDO conectada a la base de datos.
 * Lanza PDOException si la conexión falla.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En desarrollo se muestra el error; en producción log y mensaje genérico
            error_log('Error de conexión DB: ' . $e->getMessage());
            die(json_encode([
                'error' => true,
                'mensaje' => 'No se pudo conectar a la base de datos. Verifica la configuración.'
            ]));
        }
    }

    return $pdo;
}
