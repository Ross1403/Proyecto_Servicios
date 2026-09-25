<?php
// ============================================================
// admin/proyectos/editar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/proyectos/listar.php');
    exit;
}

$activePage = 'proyectos';
$errors     = [];
$db         = getDB();

// Obtener datos del proyecto
$stmtP = $db->prepare("SELECT * FROM proyectos WHERE id_proyecto = :id");
$stmtP->execute([':id' => $id]);
$proyecto = $stmtP->fetch();

if (!$proyecto) {
    header('Location: /Proyecto_Servicios/admin/proyectos/listar.php');
    exit;
}

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
    
    if (!$id_cliente) $errors[] = 'Debes seleccionar un cliente.';
    if (empty($nombre)) $errors[] = 'El nombre del proyecto es obligatorio.';
    
    $fecha_inicio = !empty($fecha_inicio) ? $fecha_inicio : null;
    $fecha_fin    = !empty($fecha_fin) ? $fecha_fin : null;

    // Actualización de imagen (opcional)
    $imagen_actual = $proyecto['imagen'];
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
            $nuevo_filename = 'img_proj_' . uniqid() . '.' . $ext;
            $destPath = __DIR__ . '/../../uploads/proyectos/' . $nuevo_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Borrar imagen anterior si existía
                if ($imagen_actual && file_exists(__DIR__ . '/../../uploads/proyectos/' . $imagen_actual)) {
                    unlink(__DIR__ . '/../../uploads/proyectos/' . $imagen_actual);
                }
                $imagen_actual = $nuevo_filename;
            } else {
                $errors[] = 'Error al subir la nueva imagen.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE proyectos SET 
                    id_cliente = :id_cliente, 
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    tecnologias = :tecnologias, 
                    fecha_inicio = :fecha_inicio, 
                    fecha_fin = :fecha_fin, 
                    estado = :estado, 
                    imagen = :imagen, 
                    destacado = :destacado
                WHERE id_proyecto = :id
            ");
            $stmt->execute([
                ':id_cliente'  => $id_cliente,
                ':nombre'      => $nombre,
                ':descripcion' => $descripcion ?: null,
                ':tecnologias' => $tecnologias ?: null,
                ':fecha_inicio'=> $fecha_inicio,
                ':fecha_fin'   => $fecha_fin,
                ':estado'      => $estado,
                ':imagen'      => $imagen_actual,
                ':destacado'   => $destacado,
                ':id'          => $id
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Proyecto actualizado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/proyectos/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
    
    // Rellenar variable $proyecto con los datos enviados si hay error (para que no se pierdan en la vista)
    $proyecto = array_merge($proyecto, $_POST);
    $proyecto['imagen'] = $imagen_actual;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto | Devioz Admin</title>
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
    <span class="topbar-title">Editar Proyecto</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/proyectos/listar.php">Proyectos</a></li>
                <li>Editar</li>
            </ul>
            <h1 class="admin-page-title">Editar Proyecto</h1>
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
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>" <?= $proyecto['id_cliente'] == $c['id_cliente'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre del proyecto *</label>
                            <input type="text" name="nombre" class="form-control-admin" required value="<?= htmlspecialchars($proyecto['nombre'] ?? '') ?>">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción</label>
                            <textarea name="descripcion" class="form-control-admin" rows="4"><?= htmlspecialchars($proyecto['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Tecnologías</label>
                            <input type="text" name="tecnologias" class="form-control-admin" value="<?= htmlspecialchars($proyecto['tecnologias'] ?? '') ?>">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Fecha de inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control-admin" value="<?= htmlspecialchars($proyecto['fecha_inicio'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Fecha de fin</label>
                                    <input type="date" name="fecha_fin" class="form-control-admin" value="<?= htmlspecialchars($proyecto['fecha_fin'] ?? '') ?>">
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
                                <option value="<?= htmlspecialchars($e) ?>" <?= $proyecto['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Destacado</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="destacado" <?= !empty($proyecto['destacado']) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group-admin">
                            <label class="form-label-admin">Imagen principal (Banner)</label>
                            <div class="upload-zone">
                                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-preview-target="imgPreview">
                                <div class="upload-icon"><i class="bi bi-image"></i></div>
                                <p class="upload-text">Cambiar imagen<br><small>Opcional. JPG, PNG (Máx. 2MB)</small></p>
                            </div>
                            <div class="upload-preview mt-2">
                                <?php if ($proyecto['imagen'] && file_exists(__DIR__ . '/../../uploads/proyectos/' . $proyecto['imagen'])): ?>
                                    <img id="imgPreview" src="/Proyecto_Servicios/uploads/proyectos/<?= htmlspecialchars($proyecto['imagen']) ?>" alt="Vista previa" style="width:100%;border-radius:8px">
                                <?php else: ?>
                                    <img id="imgPreview" src="" alt="Vista previa" style="display:none;width:100%;border-radius:8px">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-3">
            <button type="submit" class="btn-admin-primary">
                <i class="bi bi-save-fill"></i> Actualizar proyecto
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
