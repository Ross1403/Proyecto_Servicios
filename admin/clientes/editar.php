<?php
// ============================================================
// admin/clientes/editar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
    exit;
}

$activePage = 'clientes';
$errors     = [];
$db         = getDB();

$stmtC = $db->prepare("SELECT * FROM clientes WHERE id_cliente = :id");
$stmtC->execute([':id' => $id]);
$cliente = $stmtC->fetch();

if (!$cliente) {
    header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $sector      = trim($_POST['sector'] ?? '');
    $web         = trim($_POST['web'] ?? '');
    $estado      = isset($_POST['estado']) ? 1 : 0;
    $destacado   = isset($_POST['destacado']) ? 1 : 0;

    if (empty($nombre)) $errors[] = 'El nombre del cliente es obligatorio.';

    // Subida de logo
    $logo_actual = $cliente['logo'];
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['logo'];
        $maxSize   = 1024 * 1024;
        $allowedExt = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $errors[] = 'El logo no debe superar 1MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Solo JPG, PNG, SVG o WEBP permitidos.';
        } else {
            $nuevo_filename = 'logo_cli_' . uniqid() . '.' . $ext;
            $destPath = __DIR__ . '/../../uploads/clientes/' . $nuevo_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                if ($logo_actual && file_exists(__DIR__ . '/../../uploads/clientes/' . $logo_actual)) {
                    unlink(__DIR__ . '/../../uploads/clientes/' . $logo_actual);
                }
                $logo_actual = $nuevo_filename;
            } else {
                $errors[] = 'Error al subir el nuevo logo.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE clientes SET 
                    nombre = :nombre, descripcion = :descripcion, sector = :sector,
                    web = :web, logo = :logo, estado = :estado, destacado = :destacado
                WHERE id_cliente = :id
            ");
            $stmt->execute([
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion ?: null,
                ':sector'      => $sector ?: null,
                ':web'         => $web ?: null,
                ':logo'        => $logo_actual,
                ':estado'      => $estado,
                ':destacado'   => $destacado,
                ':id'          => $id
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Cliente actualizado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
    
    $cliente['nombre'] = $nombre;
    $cliente['descripcion'] = $descripcion;
    $cliente['sector'] = $sector;
    $cliente['web'] = $web;
    $cliente['estado'] = $estado;
    $cliente['destacado'] = $destacado;
    $cliente['logo'] = $logo_actual;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente | Devioz Admin</title>
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
    <span class="topbar-title">Editar Cliente</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/clientes/listar.php">Clientes</a></li>
                <li>Editar</li>
            </ul>
            <h1 class="admin-page-title">Editar Cliente</h1>
        </div>
        <a href="/Proyecto_Servicios/admin/clientes/listar.php" class="btn-admin-secondary">
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

    <form method="POST" enctype="multipart/form-data">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Datos principales</h2></div>
                    <div class="admin-card-body">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre del cliente/empresa *</label>
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($cliente['nombre']) ?>">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción o perfil de la empresa</label>
                            <textarea name="descripcion" class="form-control-admin" rows="4"><?= htmlspecialchars($cliente['descripcion']) ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Sector / Industria</label>
                                    <input type="text" name="sector" class="form-control-admin" value="<?= htmlspecialchars($cliente['sector']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Sitio web URL</label>
                                    <input type="url" name="web" class="form-control-admin" value="<?= htmlspecialchars($cliente['web']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Visibilidad e Imagen</h2></div>
                    <div class="admin-card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Estado Activo</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="estado" <?= $cliente['estado'] ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Destacado</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="destacado" <?= $cliente['destacado'] ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group-admin">
                            <label class="form-label-admin">Logotipo de la empresa</label>
                            <div class="upload-zone">
                                <input type="file" name="logo" accept="image/jpeg,image/png,image/svg+xml,image/webp" data-preview-target="imgPreview">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <p class="upload-text">Cambiar logo<br><small>Opcional. Máx 1MB</small></p>
                            </div>
                            <div class="upload-preview mt-2">
                                <?php if ($cliente['logo'] && file_exists(__DIR__ . '/../../uploads/clientes/' . $cliente['logo'])): ?>
                                    <img id="imgPreview" src="/Proyecto_Servicios/uploads/clientes/<?= htmlspecialchars($cliente['logo']) ?>" alt="Vista previa" style="width:100px;border-radius:8px">
                                <?php else: ?>
                                    <img id="imgPreview" src="" alt="Vista previa" style="display:none;width:100px;border-radius:8px">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-3">
            <button type="submit" class="btn-admin-primary">
                <i class="bi bi-save-fill"></i> Actualizar cliente
            </button>
            <a href="/Proyecto_Servicios/admin/clientes/listar.php" class="btn-admin-secondary">Cancelar</a>
        </div>
    </form>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
