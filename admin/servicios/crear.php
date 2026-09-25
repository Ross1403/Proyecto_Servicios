<?php
// ============================================================
// admin/servicios/crear.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'servicios';
$errors     = [];
$db         = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $icono       = trim($_POST['icono'] ?? '');
    $estado      = isset($_POST['estado']) ? 1 : 0;

    if (empty($nombre)) $errors[] = 'El nombre del servicio es obligatorio.';
    if (empty($descripcion)) $errors[] = 'La descripción es obligatoria.';
    if (empty($icono)) $icono = 'bi-gear'; // valor por defecto

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO servicios (nombre, descripcion, icono, estado) 
                VALUES (:nombre, :descripcion, :icono, :estado)
            ");
            $stmt->execute([
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion,
                ':icono'       => $icono,
                ':estado'      => $estado
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Servicio creado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/servicios/listar.php');
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
    <title>Nuevo Servicio | Devioz Admin</title>
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
    <span class="topbar-title">Nuevo Servicio</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/servicios/listar.php">Servicios</a></li>
                <li>Nuevo</li>
            </ul>
            <h1 class="admin-page-title">Crear Servicio</h1>
        </div>
        <a href="/Proyecto_Servicios/admin/servicios/listar.php" class="btn-admin-secondary">
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

    <div class="admin-card">
        <div class="admin-card-body">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre del servicio *</label>
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Clase de icono de Bootstrap (Ej: bi-code-slash)</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:var(--admin-input); border:1px solid var(--admin-border); color:var(--text-muted);"><i id="iconPreview" class="bi <?= htmlspecialchars($_POST['icono'] ?? 'bi-gear') ?>"></i></span>
                                <input type="text" name="icono" id="iconInput" class="form-control-admin" value="<?= htmlspecialchars($_POST['icono'] ?? 'bi-gear') ?>" style="border-top-left-radius:0; border-bottom-left-radius:0;">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción *</label>
                            <textarea name="descripcion" class="form-control-admin" rows="3" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Estado Activo</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">El servicio será visible públicamente</p>
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
                        <i class="bi bi-floppy-fill"></i> Guardar servicio
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
<script>
document.getElementById('iconInput').addEventListener('input', function(e) {
    let iconClass = e.target.value.trim();
    if (!iconClass.startsWith('bi-')) {
        iconClass = 'bi-' + iconClass;
    }
    document.getElementById('iconPreview').className = 'bi ' + iconClass;
});
</script>
</body>
</html>
