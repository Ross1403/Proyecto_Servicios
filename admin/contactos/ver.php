<?php
// ============================================================
// admin/contactos/ver.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/contactos/listar.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM contactos WHERE id_contacto = :id");
$stmt->execute([':id' => $id]);
$contacto = $stmt->fetch();

if (!$contacto) {
    header('Location: /Proyecto_Servicios/admin/contactos/listar.php');
    exit;
}

// Si el mensaje es 'Nuevo', marcarlo automáticamente como 'Revisado'
if ($contacto['estado'] === 'Nuevo') {
    $db->prepare("UPDATE contactos SET estado = 'Revisado' WHERE id_contacto = :id")->execute([':id' => $id]);
    $contacto['estado'] = 'Revisado'; // Update para la vista
}

$activePage = 'contactos';
$estados = ['Nuevo', 'Revisado', 'Contactado', 'Cerrado'];

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Ver Mensaje | Devioz Admin</title>
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
    <span class="topbar-title">Detalle de Mensaje</span>
</div>
<div class="admin-page">
    <?php if ($flash): ?>
    <div class="alert-admin alert-admin-<?= $flash['tipo'] ?> mb-4">
        <i class="bi bi-<?= $flash['tipo'] === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?>"></i>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/contactos/listar.php">Contactos</a></li>
                <li>Ver Mensaje</li>
            </ul>
            <h1 class="admin-page-title">Mensaje de <?= htmlspecialchars($contacto['nombre']) ?></h1>
        </div>
        <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="btn-admin-secondary">
            <i class="bi bi-arrow-left"></i> Volver a la bandeja
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Contenido del Mensaje</h2>
                    <span class="badge-admin bg-secondary text-white"><?= date('d/m/Y H:i', strtotime($contacto['fecha'])) ?></span>
                </div>
                <div class="admin-card-body">
                    <div style="background: rgba(108,99,255,0.05); padding: 1.5rem; border-radius: 8px; border: 1px solid rgba(108,99,255,0.1); margin-bottom: 1.5rem;">
                        <p style="white-space: pre-line; line-height: 1.6; color: var(--text-primary); margin: 0; font-size: .95rem;">
                            <?= nl2br(htmlspecialchars($contacto['mensaje'])) ?>
                        </p>
                    </div>
                    
                    <?php if ($contacto['servicio']): ?>
                    <div class="mb-3">
                        <strong style="color: var(--text-muted); font-size: .8rem; text-transform: uppercase;">Servicio de interés:</strong><br>
                        <span style="color: var(--primary-light); font-weight: 600;"><?= htmlspecialchars($contacto['servicio']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Datos del Contacto</h2></div>
                <div class="admin-card-body">
                    <ul class="list-unstyled" style="display: flex; flex-direction: column; gap: 1rem; margin: 0;">
                        <li>
                            <strong style="color: var(--text-muted); font-size: .75rem; text-transform: uppercase; display: block;">Nombre</strong>
                            <span style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($contacto['nombre']) ?></span>
                        </li>
                        <li>
                            <strong style="color: var(--text-muted); font-size: .75rem; text-transform: uppercase; display: block;">Correo Electrónico</strong>
                            <a href="mailto:<?= htmlspecialchars($contacto['correo']) ?>" style="color: var(--primary-light); font-weight: 500; text-decoration: none;">
                                <?= htmlspecialchars($contacto['correo']) ?>
                            </a>
                        </li>
                        <?php if ($contacto['telefono']): ?>
                        <li>
                            <strong style="color: var(--text-muted); font-size: .75rem; text-transform: uppercase; display: block;">Teléfono</strong>
                            <a href="tel:<?= htmlspecialchars($contacto['telefono']) ?>" style="color: var(--text-primary); font-weight: 500; text-decoration: none;">
                                <?= htmlspecialchars($contacto['telefono']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($contacto['empresa']): ?>
                        <li>
                            <strong style="color: var(--text-muted); font-size: .75rem; text-transform: uppercase; display: block;">Empresa</strong>
                            <span style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($contacto['empresa']) ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Gestión de Estado</h2></div>
                <div class="admin-card-body">
                    <form action="/Proyecto_Servicios/admin/contactos/cambiar-estado.php" method="POST">
                        <input type="hidden" name="id" value="<?= $contacto['id_contacto'] ?>">
                        <div class="form-group-admin mb-3">
                            <label class="form-label-admin">Estado actual</label>
                            <select name="nuevo_estado" class="form-select-admin">
                                <?php foreach ($estados as $e): ?>
                                <option value="<?= htmlspecialchars($e) ?>" <?= $contacto['estado'] === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-admin-primary w-100" style="justify-content: center;">
                            <i class="bi bi-save me-2"></i> Actualizar Estado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
