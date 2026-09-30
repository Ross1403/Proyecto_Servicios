<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // 1. Limpiar tabla de usuarios
    $db->query("TRUNCATE TABLE usuarios");
    
    // 2. Crear admin con datos reales y hash seguro
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES (?, ?, ?, ?, ?)");
    
    $nombre = "Administrador Principal";
    $correo = "admin@devioz.com";
    $password_plana = "Admin2024!";
    $hash = password_hash($password_plana, PASSWORD_DEFAULT);
    $rol = "admin";
    $estado = 1;
    
    $stmt->execute([$nombre, $correo, $hash, $rol, $estado]);
    
    echo "<h2>✅ Administrador creado con exito</h2>";
    echo "<p>Correo: <b>$correo</b></p>";
    echo "<p>Contraseña: <b>$password_plana</b></p>";
    echo "<a href='/Proyecto_Servicios/admin/login.php'>Ir al Login</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
