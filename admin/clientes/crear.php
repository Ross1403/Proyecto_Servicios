<?php
// ============================================================
// admin/clientes/crear.php — Crear nuevo cliente con subida de logo
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'clientes';
$errors     = [];
$success    = false;

// Sectores para el select
$db       = getDB();
$sectores = $db->query("SELECT DISTINCT sector FROM clientes WHERE sector IS NOT NULL ORDER BY sector")->fetchAll(PDO::FETCH_COLUMN);

// ============================================================
// POST — Procesar formulario
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos
    $nombre         = trim($_POST['nombre']          ?? '');
    $descripcion    = trim($_POST['descripcion']     ?? '');
    $sector         = trim($_POST['sector']          ?? '');
    $web            = trim($_POST['web']             ?? '');
    $estado         = isset($_POST['estado'])        ? 1 : 0;
    $destacado      = isset($_POST['destacado'])     ? 1 : 0;
    $fecha_registro = trim($_POST['fecha_registro']  ?? date('Y-m-d'));
    $logo_filename  = null;

    // ---- Validaciones ----
    if (empty($nombre))    $errors[] = 'El nombre del cliente es obligatorio.';
    if ($web && !filter_var($web, FILTER_VALIDATE_URL)) $errors[] = 'La URL del sitio web no es válida.';
    if ($fecha_registro && !DateTime::createFromFormat('Y-m-d', $fecha_registro)) $errors[] = 'Fecha de registro inválida.';

    // ---- Subida de logo ----
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['logo'];
        $maxSize   = 2 * 1024 * 1024; // 2MB
        $allowed   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ($file['size'] > $maxSize) {
            $errors[] = 'El logo no debe superar 2MB.';
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
        } elseif (!in_array(mime_content_type($file['tmp_name']), $allowed, true)) {
            $errors[] = 'El tipo de archivo no es una imagen válida.';
        } else {
            $logo_filename = 'logo_' . uniqid() . '.' . $ext;
            $destPath      = __DIR__ . '/../../uploads/clientes/' . $logo_filename;

            if (!is_dir(__DIR__ . '/../../uploads/clientes/')) {
                mkdir(__DIR__ . '/../../uploads/clientes/', 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $errors[] = 'Error al guardar el logo. Verifica los permisos de la carpeta uploads/.';
                $logo_filename = null;
            }
        }
    }

    // ---- Insertar en BD ----
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO clientes (nombre, descripcion, sector, logo, web, estado, destacado, fecha_registro)
                VALUES (:nombre, :descripcion, :sector, :logo, :web, :estado, :destacado, :fecha_registro)
            ");
            $stmt->execute([
                ':nombre'         => $nombre,
                ':descripcion'    => $descripcion ?: null,
                ':sector'         => $sector ?: null,
                ':logo'           => $logo_filename,
                ':web'            => $web ?: null,
                ':estado'         => $estado,
                ':destacado'      => $destacado,
                ':fecha_registro' => $fecha_registro,
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => "Cliente '{$nombre}' creado exitosamente."];
            header('Location: /Proyecto_Servicios/admin/clientes/listar.php');
            exit;
        } catch (PDOException $e) {
            error_log('Error crear cliente: ' . $e->getMessage());
            $errors[] = 'Error al guardar en la base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Nuevo cliente | Devioz Admin</title>
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
    <span class="topbar-title">Nuevo cliente</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/clientes/listar.php">Clientes</a></li>
                <li>Nuevo</li>
            </ul>
            <h1 class="admin-page-title">Nuevo cliente</h1>
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

    <form method="POST" enctype="multipart/form-data" data-validate>
        <div class="row g-4">
            <!-- Columna principal -->
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Información del cliente</h2></div>
                    <div class="admin-card-body">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Nombre del cliente *</label>
                            <input type="text" name="nombre" class="form-control-admin" required placeholder="Ej: BCP — Banco de Crédito" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción</label>
                            <textarea name="descripcion" class="form-control-admin" rows="4" placeholder="Descripción del cliente y su relación con Devioz..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Sector</label>
                                    <input type="text" name="sector" class="form-control-admin" list="sectores-list" placeholder="Ej: Banca y Finanzas" value="<?= htmlspecialchars($_POST['sector'] ?? '') ?>">
                                    <datalist id="sectores-list">
                                        <?php foreach ($sectores as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Sitio web</label>
                                    <input type="url" name="web" class="form-control-admin" placeholder="https://www.empresa.com" value="<?= htmlspecialchars($_POST['web'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-admin">
                                    <label class="form-label-admin">Fecha de registro *</label>
                                    <input type="date" name="fecha_registro" class="form-control-admin" required value="<?= htmlspecialchars($_POST['fecha_registro'] ?? date('Y-m-d')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna lateral -->
            <div class="col-lg-4">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Logo</h2></div>
                    <div class="admin-card-body">
                        <div class="upload-zone">
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" data-preview-target="logoPreview">
                            <div class="upload-icon"><i class="bi bi-image"></i></div>
                            <p class="upload-text">Haz clic o arrastra una imagen<br><small>JPG, PNG, WEBP — máx. 2MB</small></p>
                        </div>
                        <div class="upload-preview mt-2">
                            <img id="logoPreview" src="" alt="Vista previa" style="display:none;max-height:100px;border-radius:8px">
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header"><h2 class="admin-card-title">Estado</h2></div>
                    <div class="admin-card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Activo</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">Visible en el sitio público</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="estado" <?= (!isset($_POST['estado']) || $_POST['estado']) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Destacado en home</p>
                                <p style="margin:0;font-size:.78rem;color:var(--text-muted)">Aparece en la página de inicio</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="destacado" <?= isset($_POST['destacado']) ? 'checked' : '' ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3 mt-3">
            <button type="submit" class="btn-admin-primary">
                <i class="bi bi-floppy-fill"></i> Guardar cliente
            </button>
            <a href="/Proyecto_Servicios/admin/clientes/listar.php" class="btn-admin-secondary">
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
