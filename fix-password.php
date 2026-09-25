<?php
// ============================================================
// fix-password.php — Script de diagnóstico y corrección
// Ejecutar UNA SOLA VEZ en: http://localhost/Proyecto_Servicios/fix-password.php
// ELIMINAR este archivo después de usarlo por seguridad.
// ============================================================
require_once __DIR__ . '/config/database.php';

$newPassword = 'Admin2024!';
$newHash     = password_hash($newPassword, PASSWORD_DEFAULT);

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>Fix Password | Devioz</title>
<style>
  body{font-family:monospace;background:#0d0d1a;color:#f0f0f5;padding:2rem;max-width:700px;margin:0 auto}
  .ok{color:#00D4AA} .err{color:#FF4D6A} .box{background:#1a1a2e;border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:1.5rem;margin:1rem 0}
  h1{color:#8B85FF} pre{background:#13131f;padding:1rem;border-radius:6px;overflow:auto}
  a{color:#8B85FF} .btn{display:inline-block;background:#6C63FF;color:#fff;padding:.6rem 1.2rem;border-radius:8px;text-decoration:none;margin-top:1rem}
</style></head><body>';

echo '<h1>🔧 Devioz — Diagnóstico de acceso</h1>';

// 1. Test conexión BD
echo '<div class="box"><h3>1. Conexión a la base de datos</h3>';
try {
    $db = getDB();
    echo '<p class="ok">✅ Conexión exitosa a <strong>devioz_proyectos</strong></p>';
} catch (Exception $e) {
    echo '<p class="err">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Verifica que XAMPP esté corriendo y que hayas importado <code>database.sql</code></p>';
    echo '</div></body></html>';
    exit;
}
echo '</div>';

// 2. Verificar tabla usuarios
echo '<div class="box"><h3>2. Tabla de usuarios</h3>';
try {
    $usuarios = $db->query("SELECT id_usuario, nombre, correo, LEFT(password,30) AS pass_preview, rol, estado FROM usuarios")->fetchAll();
    if (empty($usuarios)) {
        echo '<p class="err">❌ La tabla usuarios está VACÍA. Debes importar database.sql primero.</p>';
    } else {
        echo '<p class="ok">✅ ' . count($usuarios) . ' usuario(s) encontrado(s)</p>';
        echo '<pre>';
        foreach ($usuarios as $u) {
            echo "ID: {$u['id_usuario']} | {$u['correo']} | Rol: {$u['rol']} | Estado: {$u['estado']}\n";
            echo "   Hash actual (primeros 30 chars): {$u['pass_preview']}...\n\n";
        }
        echo '</pre>';
    }
} catch (Exception $e) {
    echo '<p class="err">❌ Error: Tabla usuarios no existe. Importa database.sql desde phpMyAdmin.</p>';
    echo '</div></body></html>';
    exit;
}
echo '</div>';

// 3. Test del hash actual
echo '<div class="box"><h3>3. Verificación del hash</h3>';
$userTest = $db->query("SELECT * FROM usuarios WHERE correo = 'admin@devioz.com' LIMIT 1")->fetch();
if ($userTest) {
    $testResult = password_verify('password', $userTest['password']);
    if ($testResult) {
        echo '<p class="ok">✅ El hash funciona correctamente con la contraseña <strong>"password"</strong></p>';
        echo '<p>Si aun así no puedes entrar, el problema puede ser de sesiones PHP o redirección. Prueba en modo incógnito.</p>';
    } else {
        echo '<p class="err">❌ El hash NO coincide con "password". Se procederá a corregir.</p>';
    }
} else {
    echo '<p class="err">❌ Usuario admin@devioz.com no encontrado.</p>';
}
echo '</div>';

// 4. Acción: Actualizar passwords
if (isset($_GET['fix']) && $_GET['fix'] === '1') {
    echo '<div class="box"><h3>4. Aplicando corrección...</h3>';

    $hash1 = password_hash('Admin2024!', PASSWORD_DEFAULT);
    $hash2 = password_hash('password',   PASSWORD_DEFAULT);

    try {
        // Admin
        $stmtA = $db->prepare("UPDATE usuarios SET password = :h WHERE correo = 'admin@devioz.com'");
        $stmtA->execute([':h' => $hash1]);
        echo '<p class="ok">✅ admin@devioz.com → contraseña: <strong>Admin2024!</strong></p>';

        // Editor
        $stmtE = $db->prepare("UPDATE usuarios SET password = :h WHERE correo = 'editor@devioz.com'");
        $stmtE->execute([':h' => $hash2]);
        echo '<p class="ok">✅ editor@devioz.com → contraseña: <strong>password</strong></p>';

        // Verificar
        $check = $db->query("SELECT correo, password FROM usuarios WHERE correo = 'admin@devioz.com'")->fetch();
        if (password_verify('Admin2024!', $check['password'])) {
            echo '<p class="ok">✅ Verificación exitosa. ¡Ahora puedes iniciar sesión!</p>';
        }

    } catch (Exception $e) {
        echo '<p class="err">❌ Error al actualizar: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';

    echo '<div class="box" style="border-color:#00D4AA">';
    echo '<h3 class="ok">✅ Credenciales actualizadas</h3>';
    echo '<table style="width:100%;border-collapse:collapse">';
    echo '<tr><th style="text-align:left;padding:.4rem;border-bottom:1px solid rgba(255,255,255,.1)">Usuario</th><th style="text-align:left;padding:.4rem;border-bottom:1px solid rgba(255,255,255,.1)">Contraseña</th></tr>';
    echo '<tr><td style="padding:.4rem">admin@devioz.com</td><td style="padding:.4rem;color:#00D4AA"><strong>Admin2024!</strong></td></tr>';
    echo '<tr><td style="padding:.4rem">editor@devioz.com</td><td style="padding:.4rem;color:#00D4AA"><strong>password</strong></td></tr>';
    echo '</table>';
    echo '<a class="btn" href="/Proyecto_Servicios/admin/login.php">→ Ir al login del admin</a>';
    echo '<p style="color:#FF4D6A;margin-top:1rem;font-size:.85rem">⚠️ ELIMINA este archivo (fix-password.php) después de usarlo.</p>';
    echo '</div>';

} else {
    echo '<div class="box">';
    echo '<h3>4. Acción requerida</h3>';
    echo '<p>Haz clic en el botón para regenerar los hashes de contraseña correctamente:</p>';
    echo '<a class="btn" href="?fix=1">🔧 Corregir contraseñas ahora</a>';
    echo '</div>';
}

// Info adicional
echo '<div class="box" style="border-color:rgba(255,176,32,.3)">';
echo '<h3 style="color:#FFB020">ℹ️ Info de sesiones PHP</h3>';
echo '<pre>';
echo 'session_save_path: ' . session_save_path() . "\n";
echo 'PHP version: '       . PHP_VERSION . "\n";
echo 'XAMPP detectado: '   . (file_exists('C:/xampp/php/php.ini') ? 'Sí' : 'No seguro') . "\n";
echo '</pre>';
echo '</div>';

echo '</body></html>';
