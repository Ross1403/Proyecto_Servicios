<?php
// ============================================================
// senior_migration.php — Script para actualizar la Base de Datos
// Ejecutar esto es necesario para: Roles (RBAC), Borrado Lógico,
// Programación de publicaciones y Drag & Drop.
// ============================================================
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    echo "Iniciando migración Senior Pro...\n";

    // 1. Añadir Rol y Soft Delete a Usuarios
    $db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS rol ENUM('SuperAdmin', 'Editor', 'Visualizador') DEFAULT 'SuperAdmin'");
    $db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");
    echo "- Tabla usuarios actualizada (Roles y Soft Delete)\n";

    // 2. Añadir Orden y Fecha de Publicación a Proyectos
    $db->exec("ALTER TABLE proyectos ADD COLUMN IF NOT EXISTS orden INT DEFAULT 0");
    $db->exec("ALTER TABLE proyectos ADD COLUMN IF NOT EXISTS fecha_publicacion DATETIME NULL");
    $db->exec("ALTER TABLE proyectos ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");
    echo "- Tabla proyectos actualizada (Drag&Drop, Cron y Soft Delete)\n";

    // 3. Añadir Soft Delete a Clientes
    $db->exec("ALTER TABLE clientes ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");
    echo "- Tabla clientes actualizada (Soft Delete)\n";

    echo "\n¡Migración exitosa! Tu base de datos ahora soporta características nivel Enterprise.";

} catch (PDOException $e) {
    echo "Error en la migración: " . $e->getMessage();
}
