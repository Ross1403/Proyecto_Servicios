<?php
// ============================================================
// admin/servicios/editar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/servicios/listar.php');
    exit;
}

$activePage = 'servicios';
$errors     = [];
$db         = getDB();

$stmtS = $db->prepare("SELECT * FROM servicios WHERE id_servicio = :id");
$stmtS->execute([':id' => $id]);
$servicio = $stmtS->fetch();

if (!$servicio) {
    header('Location: /Proyecto_Servicios/admin/servicios/listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $icono       = trim($_POST['icono'] ?? '');
    $estado      = isset($_POST['estado']) ? 1 : 0;

    if (empty($nombre)) $errors[] = 'El nombre del servicio es obligatorio.';
    if (empty($descripcion)) $errors[] = 'La descripción es obligatoria.';
    if (empty($icono)) $icono = 'bi-gear';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE servicios SET 
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    icono = :icono, 
                    estado = :estado 
                WHERE id_servicio = :id
            ");
            $stmt->execute([
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion,
                ':icono'       => $icono,
                ':estado'      => $estado,
                ':id'          => $id
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Servicio actualizado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/servicios/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
    
    $servicio['nombre'] = $nombre;
    $servicio['descripcion'] = $descripcion;
    $servicio['icono'] = $icono;
    $servicio['estado'] = $estado;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio | Devioz Admin</title>
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
    <span class="topbar-title">Editar Servicio</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/servicios/listar.php">Servicios</a></li>
                <li>Editar</li>
            </ul>
            <h1 class="admin-page-title">Editar Servicio</h1>
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
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($servicio['nombre']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Icono del servicio</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:var(--admin-input); border:1px solid var(--admin-border); color:var(--text-muted);"><i id="iconPreview" class="bi <?= htmlspecialchars($servicio['icono']) ?>"></i></span>
                                <select name="icono" id="iconInput" class="form-control-admin" style="border-top-left-radius:0; border-bottom-left-radius:0;">
                                    <?php
                                    $icons = [
                                        'bi-gear' => 'Engranaje (General)',
                                        'bi-code-slash' => 'Código (Desarrollo Web)',
                                        'bi-laptop' => 'Laptop (Tecnología)',
                                        'bi-phone' => 'Teléfono (Móvil)',
                                        'bi-cloud' => 'Nube (Cloud / Servidores)',
                                        'bi-shield-check' => 'Escudo (Seguridad)',
                                        'bi-graph-up' => 'Gráfico (Marketing / SEO)',
                                        'bi-headset' => 'Auriculares (Soporte)',
                                        'bi-bezier2' => 'Vector (Diseño UI/UX)',
                                        'bi-cart3' => 'Carrito (E-commerce)',
                                        'bi-server' => 'Servidor (Infraestructura)',
                                        'bi-database' => 'Base de datos'
                                    ];
                                    $selectedIcon = $servicio['icono'] ?? 'bi-gear';
                                    foreach ($icons as $class => $label) {
                                        $sel = ($class === $selectedIcon) ? 'selected' : '';
                                        echo "<option value=\"$class\" $sel>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción *</label>
                            <textarea name="descripcion" class="form-control-admin" rows="3" required><?= htmlspecialchars($servicio['descripcion']) ?></textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Estado Activo</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">El servicio será visible públicamente</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="estado" <?= $servicio['estado'] ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3" style="border-top:1px solid var(--admin-border);">
                    <button type="submit" class="btn-admin-primary">
                        <i class="bi bi-save-fill"></i> Actualizar servicio
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
