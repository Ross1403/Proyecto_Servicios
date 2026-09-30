<?php
// ============================================================
// admin/usuarios/editar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['admin_rol'] !== 'admin') {
    header('Location: /Proyecto_Servicios/admin/dashboard.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/usuarios/listar.php');
    exit;
}

$activePage = 'usuarios';
$errors     = [];
$db         = getDB();

$stmtU = $db->prepare("SELECT id_usuario, nombre, correo, rol, estado FROM usuarios WHERE id_usuario = :id");
$stmtU->execute([':id' => $id]);
$usuario = $stmtU->fetch();

if (!$usuario) {
    header('Location: /Proyecto_Servicios/admin/usuarios/listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol      = $_POST['rol'] ?? 'editor';
    $estado   = isset($_POST['estado']) ? 1 : 0;

    // Proteger al propio administrador para no quitarse admin ni desactivarse por error
    if ($id === $_SESSION['admin_id']) {
        $rol = 'admin';
        $estado = 1;
    }

    if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo no válido.';
    if (!empty($password) && strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if (!in_array($rol, ['admin', 'editor'])) $rol = 'editor';

    $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario != :id");
    $stmtCheck->execute([':correo' => $correo, ':id' => $id]);
    if ($stmtCheck->fetch()) {
        $errors[] = 'Ya existe otro usuario con ese correo electrónico.';
    }

    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE usuarios SET nombre = :n, correo = :c, password = :p, rol = :r, estado = :e WHERE id_usuario = :id
                ");
                $stmt->execute([':n'=>$nombre, ':c'=>$correo, ':p'=>$hash, ':r'=>$rol, ':e'=>$estado, ':id'=>$id]);
            } else {
                $stmt = $db->prepare("
                    UPDATE usuarios SET nombre = :n, correo = :c, rol = :r, estado = :e WHERE id_usuario = :id
                ");
                $stmt->execute([':n'=>$nombre, ':c'=>$correo, ':r'=>$rol, ':e'=>$estado, ':id'=>$id]);
            }

            // Si se editó a sí mismo, actualizar variables de sesión
            if ($id === $_SESSION['admin_id']) {
                $_SESSION['admin_nombre'] = $nombre;
                $_SESSION['admin_correo'] = $correo;
            }

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Usuario actualizado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/usuarios/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
    
    $usuario['nombre'] = $nombre;
    $usuario['correo'] = $correo;
    $usuario['rol'] = $rol;
    $usuario['estado'] = $estado;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>
<main class="admin-main">
<div class="admin-topbar">
    <button class="topbar-toggle" id="sidebarOpen"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Editar Usuario</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/usuarios/listar.php">Usuarios</a></li>
                <li>Editar</li>
            </ul>
            <h1 class="admin-page-title">Editar Usuario</h1>
        </div>
        <a href="/Proyecto_Servicios/admin/usuarios/listar.php" class="btn-admin-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert-admin alert-admin-danger mb-4">
        <div>
            <i class="bi bi-exclamation-circle-fill"></i>
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-card" style="max-width:700px">
        <div class="admin-card-body">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre completo *</label>
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Correo Electrónico *</label>
                            <input type="email" name="correo" class="form-control-admin" required value="<?= htmlspecialchars($usuario['correo']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nueva Contraseña (Opcional)</label>
                            <input type="password" name="password" class="form-control-admin" minlength="6" placeholder="Dejar en blanco para no cambiar">
                        </div>
                    </div>
                    
                    <?php if ($id === $_SESSION['admin_id']): ?>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Rol</label>
                            <input type="text" class="form-control-admin" value="Administrador (No puedes cambiar tu propio rol)" disabled>
                            <input type="hidden" name="rol" value="admin">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Usuario Activo</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">No puedes desactivar tu propia cuenta</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked disabled>
                                <span class="toggle-slider"></span>
                                <input type="hidden" name="estado" value="1">
                            </label>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Rol</label>
                            <select name="rol" class="form-select-admin">
                                <option value="editor" <?= $usuario['rol'] === 'editor' ? 'selected' : '' ?>>Editor (Acceso limitado)</option>
                                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador (Acceso total)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Usuario Activo</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">Permite que el usuario inicie sesión</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="estado" <?= $usuario['estado'] ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3" style="border-top:1px solid var(--admin-border);">
                    <button type="submit" class="btn-admin-primary">
                        <i class="bi bi-save-fill"></i> Actualizar usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
