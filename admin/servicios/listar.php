<?php
// ============================================================
// admin/servicios/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'servicios';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM servicios WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND nombre LIKE :q";
    $params[':q'] = '%' . $q . '%';
}
$sql .= " ORDER BY nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$servicios = $stmt->fetchAll();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios | Devioz Admin</title>
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
    <span class="topbar-title">Servicios</span>
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
            <h1 class="admin-page-title">Gestión de Servicios</h1>
            <p class="admin-page-subtitle"><?= count($servicios) ?> servicio(s)</p>
        </div>
        <a href="/Proyecto_Servicios/admin/servicios/crear.php" class="btn-admin-primary">
            <i class="bi bi-plus-lg"></i> Nuevo servicio
        </a>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label-admin">Buscar</label>
                    <input type="text" name="q" class="form-control-admin" placeholder="Nombre del servicio..." value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q): ?>
                    <a href="/Proyecto_Servicios/admin/servicios/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="admin-card">
        <div style="overflow-x:auto">
            <?php if (empty($servicios)): ?>
            <div class="empty-state"><i class="bi bi-gear"></i><h5>Sin servicios</h5><p>No hay servicios registrados.</p></div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th style="width: 50px;">Icono</th>
                        <th>Nombre del Servicio</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $s): ?>
                    <tr>
                        <td class="text-center">
                            <div style="background:rgba(108,99,255,.1); width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--primary-light); font-size:1.2rem;">
                                <i class="bi <?= htmlspecialchars($s['icono'] ?? 'bi-gear') ?>"></i>
                            </div>
                        </td>
                        <td>
                            <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($s['nombre']) ?></p>
                            <p style="margin:0;font-size:.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:300px;">
                                <?= htmlspecialchars($s['descripcion']) ?>
                            </p>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <label class="toggle-switch" title="<?= $s['estado'] ? 'Desactivar' : 'Activar' ?>">
                                    <input type="checkbox"
                                           <?= $s['estado'] ? 'checked' : '' ?>
                                           data-toggle-url="/Proyecto_Servicios/admin/clientes/toggle-estado.php"
                                           data-toggle-id="<?= $s['id_servicio'] ?>"
                                           data-toggle-table="servicios"
                                           data-toggle-field="estado">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="badge-admin <?= $s['estado'] ? 'badge-active' : 'badge-inactive' ?>" data-estado-badge>
                                    <?= $s['estado'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </div>
                        </td>
                        <td>#<?= $s['id_servicio'] ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/servicios/editar.php?id=<?= $s['id_servicio'] ?>" class="btn-admin-edit btn-admin-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/servicios/eliminar.php?id=<?= $s['id_servicio'] ?>"
                                        data-delete-name="<?= htmlspecialchars($s['nombre']) ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
</div>

<!-- Modal eliminar -->
<div class="modal fade modal-admin" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar el servicio <strong id="deleteItemName"></strong>?</p>
                <p style="font-size:.85rem;color:var(--danger)"><i class="bi bi-exclamation-circle me-1"></i>Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" class="btn-admin-danger" id="confirmDeleteBtn"><i class="bi bi-trash3"></i> Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
