<?php
// ============================================================
// admin/usuarios/crear.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['admin_rol'] !== 'admin') {
    header('Location: /Proyecto_Servicios/admin/dashboard.php');
    exit;
}

$activePage = 'usuarios';
$errors     = [];
$db         = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol      = $_POST['rol'] ?? 'editor';
    $estado   = isset($_POST['estado']) ? 1 : 0;

    if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo no válido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if (!in_array($rol, ['admin', 'editor'])) $rol = 'editor';

    // Verificar si el correo ya existe
    $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
    $stmtCheck->execute([':correo' => $correo]);
    if ($stmtCheck->fetch()) {
        $errors[] = 'Ya existe un usuario con ese correo electrónico.';
    }

    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO usuarios (nombre, correo, password, rol, estado) 
                VALUES (:nombre, :correo, :password, :rol, :estado)
            ");
            $stmt->execute([
                ':nombre'   => $nombre,
                ':correo'   => $correo,
                ':password' => $hash,
                ':rol'      => $rol,
                ':estado'   => $estado
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Usuario creado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/usuarios/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario | Devioz Admin</title>
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
    <span class="topbar-title">Nuevo Usuario</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/usuarios/listar.php">Usuarios</a></li>
                <li>Nuevo</li>
            </ul>
            <h1 class="admin-page-title">Crear Usuario</h1>
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
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Correo Electrónico *</label>
                            <input type="email" name="correo" class="form-control-admin" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Contraseña *</label>
                            <input type="password" name="password" class="form-control-admin" required minlength="6">
                            <small style="color:var(--text-muted);font-size:.75rem;">Mínimo 6 caracteres.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Rol</label>
                            <select name="rol" class="form-select-admin">
                                <option value="editor" <?= ($_POST['rol'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor (Acceso limitado)</option>
                                <option value="admin" <?= ($_POST['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador (Acceso total)</option>
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
                                <input type="checkbox" name="estado" <?= (!isset($_POST['estado']) || $_POST['estado']) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3" style="border-top:1px solid var(--admin-border);">
                    <button type="submit" class="btn-admin-primary">
                        <i class="bi bi-floppy-fill"></i> Crear usuario
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
