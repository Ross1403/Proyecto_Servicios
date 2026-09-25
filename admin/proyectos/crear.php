<?php
// ============================================================
// admin/proyectos/crear.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'proyectos';
$errors     = [];
$db         = getDB();

// Obtener clientes para el select
$clientes = $db->query("SELECT id_cliente, nombre FROM clientes ORDER BY nombre ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente   = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $tecnologias  = trim($_POST['tecnologias'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $fecha_fin    = trim($_POST['fecha_fin'] ?? '');
    $estado       = trim($_POST['estado'] ?? 'Planificado');
    $destacado    = isset($_POST['destacado']) ? 1 : 0;
    $imagen_filename = null;

    if (!$id_cliente) $errors[] = 'Debes seleccionar un cliente.';
    if (empty($nombre)) $errors[] = 'El nombre del proyecto es obligatorio.';
    
    $fecha_inicio = !empty($fecha_inicio) ? $fecha_inicio : null;
    $fecha_fin    = !empty($fecha_fin) ? $fecha_fin : null;

    // Subida de imagen
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['imagen'];
        $maxSize   = 2 * 1024 * 1024;
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $errors[] = 'La imagen no debe superar 2MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Solo JPG, PNG o WEBP permitidos.';
        } else {
            $imagen_filename = 'img_proj_' . uniqid() . '.' . $ext;
            $destPath = __DIR__ . '/../../uploads/proyectos/' . $imagen_filename;
            
            if (!is_dir(__DIR__ . '/../../uploads/proyectos/')) {
                mkdir(__DIR__ . '/../../uploads/proyectos/', 0755, true);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $errors[] = 'Error al subir la imagen.';
                $imagen_filename = null;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO proyectos (id_cliente, nombre, descripcion, tecnologias, fecha_inicio, fecha_fin, estado, imagen, destacado) 
                VALUES (:id_cliente, :nombre, :descripcion, :tecnologias, :fecha_inicio, :fecha_fin, :estado, :imagen, :destacado)
            ");
            $stmt->execute([
                ':id_cliente'  => $id_cliente,
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion ?: null,
                ':tecnologias' => $tecnologias ?: null,
                ':fecha_inicio'=> $fecha_inicio,
                ':fecha_fin'   => $fecha_fin,
                ':estado'      => $estado,
                ':imagen'      => $imagen_filename,
                ':destacado'   => $destacado
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Proyecto creado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/proyectos/listar.php');
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
    <title>Nuevo Proyecto | Devioz Admin</title>
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
    <span class="topbar-title">Nuevo Proyecto</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/proyectos/listar.php">Proyectos</a></li>
                <li>Nuevo</li>
            </ul>
            <h1 class="admin-page-title">Crear Proyecto</h1>
        </div>
        <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-secondary">
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
                    <div class="admin-card-header"><h2 class="admin-card-title">Datos del proyecto</h2></div>
                    <div class="admin-card-body">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Cliente *</label>
                            <select name="id_cliente" class="form-select-admin" required>
                                <option value="">Selecciona un cliente...</option>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>" <?= ($_POST['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre del proyecto *</label>
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción</label>
                            <textarea name="descripcion" class="form-control-admin" rows="4"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Tecnologías (separadas por comas)</label>
                            <input type="text" name="tecnologias" class="form-control-admin" placeholder="Ej: PHP, React, MySQL" value="<?= htmlspecialchars($_POST['tecnologias'] ?? '') ?>">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Fecha de inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control-admin" value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Fecha de fin (dejar vacío si está en curso)</label>
                                    <input type="date" name="fecha_fin" class="form-control-admin" value="<?= htmlspecialchars($_POST['fecha_fin'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Estado e Imagen</h2></div>
                    <div class="admin-card-body">
                        <div class="form-group-admin mb-4">
                            <label class="form-label-admin">Estado del proyecto</label>
                            <select name="estado" class="form-select-admin">
                                <?php 
                                $estados = ['Planificado', 'En desarrollo', 'Finalizado', 'Suspendido'];
                                foreach ($estados as $e): ?>
                                <option value="<?= htmlspecialchars($e) ?>" <?= ($_POST['estado'] ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Destacado (Caso de Éxito)</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="destacado" <?= isset($_POST['destacado']) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group-admin">
                            <label class="form-label-admin">Imagen principal (Banner)</label>
                            <div class="upload-zone">
                                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-preview-target="imgPreview">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <p class="upload-text">Subir imagen<br><small>JPG, PNG, WEBP (Máx. 2MB)</small></p>
                            </div>
                            <div class="upload-preview mt-2">
                                <img id="imgPreview" src="" alt="Vista previa" style="display:none;width:100%;border-radius:8px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-3">
            <button type="submit" class="btn-admin-primary">
                <i class="bi bi-floppy-fill"></i> Guardar proyecto
            </button>
            <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-secondary">
                Cancelar
            </a>
        </div>
    </form>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
