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
    $problema     = trim($_POST['problema'] ?? '');
    $solucion     = trim($_POST['solucion'] ?? '');
    $resultado    = trim($_POST['resultado'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $fecha_fin    = trim($_POST['fecha_fin'] ?? '');
    $estado       = trim($_POST['estado'] ?? 'Planificado');
    $destacado    = isset($_POST['destacado']) ? 1 : 0;
    $porcentaje_avance  = filter_input(INPUT_POST, 'porcentaje_avance', FILTER_VALIDATE_INT) ?? 0;
    $porcentaje_avance  = max(0, min(100, (int)$porcentaje_avance));
    $descripcion_avance = trim($_POST['descripcion_avance'] ?? '');
    $imagen_filename    = null;
    $zip_filename       = null;
    $zip_original_name  = null;
    $zip_filesize       = null;

    if (!$id_cliente) $errors[] = 'Debes seleccionar un cliente.';
    if (empty($nombre)) $errors[] = 'El nombre del proyecto es obligatorio.';
    
    $fecha_inicio = !empty($fecha_inicio) ? $fecha_inicio : null;
    $fecha_fin    = !empty($fecha_fin) ? $fecha_fin : null;
    if ($fecha_inicio && $fecha_fin && $fecha_fin < $fecha_inicio) {
        $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
    }

    $estados_permitidos = ['Planificado', 'En desarrollo', 'Finalizado', 'Suspendido'];
    if (!in_array($estado, $estados_permitidos)) {
        $errors[] = 'Estado no válido.';
        $estado = 'Planificado';
    }

    // Subida de imagen
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['imagen'];
        $maxSize   = 2 * 1024 * 1024;
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $errors[] = 'La imagen no debe superar 2MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Solo JPG, PNG o WEBP permitidos.';
        } elseif (!in_array(mime_content_type($file['tmp_name']), $allowedMime, true)) {
            $errors[] = 'El tipo de archivo no es una imagen válida o está corrompido.';
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

    // Subida de archivo comprimido ZIP
    if (!empty($_FILES['archivo_zip']['name'])) {
        $uploadError = $_FILES['archivo_zip']['error'];
        if ($uploadError !== UPLOAD_ERR_OK && $uploadError !== UPLOAD_ERR_NO_FILE) {
            $uploadErrorsMap = [
                UPLOAD_ERR_INI_SIZE   => 'El archivo ZIP supera el límite permitido por el servidor (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE  => 'El archivo ZIP supera el límite del formulario.',
                UPLOAD_ERR_PARTIAL    => 'El archivo ZIP solo se subió parcialmente.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo ZIP en el disco.',
                UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.'
            ];
            $errors[] = $uploadErrorsMap[$uploadError] ?? 'Error desconocido al subir el archivo ZIP (Código: ' . $uploadError . ').';
        } elseif ($uploadError === UPLOAD_ERR_OK) {
            $zipFile   = $_FILES['archivo_zip'];
            $maxZipSize = 40 * 1024 * 1024; // 40MB
            $allowedZipExt = ['zip', 'rar', '7z', 'tar', 'gz'];
            $extZip    = strtolower(pathinfo($zipFile['name'], PATHINFO_EXTENSION));

            if ($zipFile['size'] > $maxZipSize) {
                $errors[] = 'El archivo comprimido no debe superar los 40MB.';
            } elseif (!in_array($extZip, $allowedZipExt, true)) {
                $errors[] = 'Formato de comprimido no permitido (.'.$extZip.'). Se admite ZIP, RAR, 7Z, TAR o GZ.';
            } else {
                $zipDir = __DIR__ . '/../../uploads/proyectos/entregables/';
                if (!is_dir($zipDir)) {
                    @mkdir($zipDir, 0777, true);
                }

                $zip_filename      = 'proj_pkg_' . uniqid() . '.' . $extZip;
                $zip_original_name = basename($zipFile['name']);
                $zip_filesize      = (int)$zipFile['size'];
                $destZipPath       = $zipDir . $zip_filename;

                if (!move_uploaded_file($zipFile['tmp_name'], $destZipPath)) {
                    $errors[] = 'Error al mover el archivo comprimido a la carpeta de destino. Verifica permisos.';
                    $zip_filename = null;
                    $zip_original_name = null;
                    $zip_filesize = null;
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO proyectos (
                    id_cliente, nombre, descripcion, tecnologias, problema, solucion, resultado, 
                    fecha_inicio, fecha_fin, estado, imagen, destacado,
                    archivo_zip, archivo_zip_nombre, archivo_zip_size, descripcion_avance, porcentaje_avance
                ) VALUES (
                    :id_cliente, :nombre, :descripcion, :tecnologias, :problema, :solucion, :resultado, 
                    :fecha_inicio, :fecha_fin, :estado, :imagen, :destacado,
                    :archivo_zip, :archivo_zip_nombre, :archivo_zip_size, :descripcion_avance, :porcentaje_avance
                )
            ");
            $stmt->execute([
                ':id_cliente'           => $id_cliente,
                ':nombre'               => $nombre,
                ':descripcion'          => $descripcion ?: null,
                ':tecnologias'          => $tecnologias ?: null,
                ':problema'             => $problema ?: null,
                ':solucion'             => $solucion ?: null,
                ':resultado'            => $resultado ?: null,
                ':fecha_inicio'         => $fecha_inicio,
                ':fecha_fin'            => $fecha_fin,
                ':estado'               => $estado,
                ':imagen'               => $imagen_filename,
                ':destacado'            => $destacado,
                ':archivo_zip'          => $zip_filename,
                ':archivo_zip_nombre'   => $zip_original_name,
                ':archivo_zip_size'     => $zip_filesize,
                ':descripcion_avance'   => $descripcion_avance ?: null,
                ':porcentaje_avance'    => $porcentaje_avance
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
                        <div class="form-group-admin">
                            <label class="form-label-admin">Problema identificado</label>
                            <textarea name="problema" class="form-control-admin" rows="3"><?= htmlspecialchars($_POST['problema'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Solución desarrollada</label>
                            <textarea name="solucion" class="form-control-admin" rows="3"><?= htmlspecialchars($_POST['solucion'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Resultado obtenido</label>
                            <textarea name="resultado" class="form-control-admin" rows="3"><?= htmlspecialchars($_POST['resultado'] ?? '') ?></textarea>
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
                        
                        <div class="form-group-admin mb-4">
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

                        <!-- Apartado de Archivo Comprimido ZIP y Avance -->
                        <div class="form-group-admin pt-3" style="border-top:1px dashed var(--admin-border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label-admin mb-0">
                                    <i class="bi bi-file-earmark-zip-fill text-warning me-1"></i> Paquete de Entrega / ZIP
                                </label>
                                <span class="badge-admin badge-dev" style="font-size:0.7rem;text-transform:none">Entregable</span>
                            </div>

                            <!-- TARJETA VISUAL EXACTA IDÉNTICA A LA IMAGEN (Aparece arriba al seleccionar) -->
                            <div id="zipVisualCard" class="p-3 mb-2 rounded" style="display:none;background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.35);">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="font-size:2.2rem;color:#f59e0b;line-height:1">
                                        <i class="bi bi-file-earmark-zip-fill"></i>
                                    </div>
                                    <div class="overflow-hidden" style="flex:1">
                                        <p id="zipVisualName" class="mb-1 text-truncate" style="font-size:.875rem;font-weight:700;color:var(--text-primary)">
                                            archivo.zip
                                        </p>
                                        <div class="d-flex align-items-center gap-3">
                                            <small id="zipVisualSize" style="color:var(--text-muted);font-size:.76rem">0.00 MB</small>
                                            <button type="button" id="btnFakeDownload" class="btn-admin-sm btn-admin-secondary" style="padding:0.2rem 0.6rem;font-size:0.72rem;cursor:default">
                                                <i class="bi bi-download"></i> Descargar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check mt-3 pt-2" style="border-top:1px dashed rgba(245,158,11,0.25)">
                                    <input class="form-check-input" type="checkbox" id="chkQuitarZipSeleccionado">
                                    <label class="form-check-label text-danger" for="chkQuitarZipSeleccionado" style="font-size:0.78rem;cursor:pointer;font-weight:500">
                                        <i class="bi bi-trash3 me-1"></i> Eliminar este archivo comprimido
                                    </label>
                                </div>
                            </div>

                            <!-- Zona de Subida / Reemplazo -->
                            <div class="upload-zone upload-zone-zip">
                                <input type="file" name="archivo_zip" id="zipFileInput" accept=".zip,.rar,.7z,.tar,.gz,application/zip,application/x-zip-compressed,multipart/x-zip">
                                <div class="upload-icon" style="color:#f59e0b"><i class="bi bi-file-earmark-zip"></i></div>
                                <p class="upload-text" id="zipUploadPrompt">
                                    Subir archivo comprimido<br>
                                    <small>ZIP, RAR, 7Z (Máx. 40MB)</small>
                                </p>
                            </div>

                            <!-- Porcentaje de Avance -->
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label-admin mb-0" style="font-size:.8rem">
                                        <i class="bi bi-speedometer2 me-1"></i> Porcentaje de Avance
                                    </label>
                                    <span id="avanceDisplay" class="badge-admin badge-fin" style="font-size:.78rem;font-weight:700">
                                        <?= (int)($_POST['porcentaje_avance'] ?? 0) ?>%
                                    </span>
                                </div>
                                <input type="range" class="form-range" name="porcentaje_avance" id="avanceRange" min="0" max="100" step="5" value="<?= (int)($_POST['porcentaje_avance'] ?? 0) ?>" style="accent-color:var(--primary)">
                            </div>

                            <!-- Descripción del Avance -->
                            <div class="mt-2">
                                <label class="form-label-admin mb-1" style="font-size:.8rem">
                                    <i class="bi bi-chat-left-text me-1"></i> Descripción del avance / Notas
                                </label>
                                <textarea name="descripcion_avance" class="form-control-admin" rows="2" placeholder="Ej: Avance al 40% con arquitectura base y módulos iniciales entregados..." style="font-size:.85rem"><?= htmlspecialchars($_POST['descripcion_avance'] ?? '') ?></textarea>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js?v=1791821764"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const zipInput      = document.getElementById('zipFileInput');
    const zipVisualCard = document.getElementById('zipVisualCard');
    const zipVisualName = document.getElementById('zipVisualName');
    const zipVisualSize = document.getElementById('zipVisualSize');
    const chkQuitar     = document.getElementById('chkQuitarZipSeleccionado');
    const uploadPrompt  = document.getElementById('zipUploadPrompt');

    if (zipInput) {
        zipInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                if (zipVisualCard) zipVisualCard.style.display = 'none';
                if (uploadPrompt) uploadPrompt.innerHTML = 'Subir archivo comprimido<br><small>ZIP, RAR, 7Z (Máx. 40MB)</small>';
                return;
            }

            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (zipVisualName) zipVisualName.textContent = file.name;
            if (zipVisualSize) zipVisualSize.textContent = `${sizeMb} MB`;
            if (chkQuitar) chkQuitar.checked = false;
            if (zipVisualCard) zipVisualCard.style.display = 'block';

            if (uploadPrompt) {
                uploadPrompt.innerHTML = 'Reemplazar archivo comprimido<br><small>ZIP, RAR, 7Z (Máx. 40MB)</small>';
            }
        });
    }

    if (chkQuitar && zipInput) {
        chkQuitar.addEventListener('change', function () {
            if (this.checked) {
                zipInput.value = '';
                if (zipVisualCard) zipVisualCard.style.display = 'none';
                if (uploadPrompt) {
                    uploadPrompt.innerHTML = 'Subir archivo comprimido<br><small>ZIP, RAR, 7Z (Máx. 40MB)</small>';
                }
            }
        });
    }
});
</script>
</body>
</html>
