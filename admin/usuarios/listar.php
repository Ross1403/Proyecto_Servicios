<?php
// ============================================================
// admin/usuarios/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

// Validar que solo el administrador principal pueda gestionar usuarios
if ($_SESSION['admin_rol'] !== 'admin') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'msg' => 'No tienes permisos para gestionar usuarios.'];
    header('Location: /Proyecto_Servicios/admin/dashboard.php');
    exit;
}

$activePage = 'usuarios';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (nombre LIKE :q OR correo LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
$sql .= " ORDER BY rol ASC, nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | Devioz Admin</title>
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
    <span class="topbar-title">Usuarios</span>
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
            <h1 class="admin-page-title">Gestión de Usuarios</h1>
            <p class="admin-page-subtitle"><?= count($usuarios) ?> usuario(s)</p>
        </div>
        <a href="/Proyecto_Servicios/admin/usuarios/crear.php" class="btn-admin-primary">
            <i class="bi bi-person-plus-fill"></i> Nuevo usuario
        </a>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label-admin">Buscar</label>
                    <input type="text" name="q" class="form-control-admin" placeholder="Nombre o correo..." value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q): ?>
                    <a href="/Proyecto_Servicios/admin/usuarios/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($usuarios)): ?>
            <div class="empty-state"><i class="bi bi-people"></i><h5>Sin usuarios</h5><p>No hay registros que coincidan.</p></div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th style="width: 50px;">Perfil</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td class="text-center">
                            <div class="table-avatar" style="width:35px;height:35px;font-size:.9rem;background:var(--primary-light);">
                                <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                            </div>
                        </td>
                        <td>
                            <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">
                                <?= htmlspecialchars($u['nombre']) ?>
                                <?php if ($u['id_usuario'] === $_SESSION['admin_id']): ?>
                                    <span class="badge bg-secondary ms-1" style="font-size:0.6rem">TÚ</span>
                                <?php endif; ?>
                            </p>
                            <p style="margin:0;font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($u['correo']) ?></p>
                        </td>
                        <td>
                            <?php if ($u['rol'] === 'admin'): ?>
                                <span style="color:var(--primary-light);font-weight:700;"><i class="bi bi-shield-lock-fill me-1"></i> Admin</span>
                            <?php else: ?>
                                <span style="color:var(--text-secondary);font-weight:600;"><i class="bi bi-pencil-square me-1"></i> Editor</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['id_usuario'] !== $_SESSION['admin_id']): ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-admin <?= $u['estado'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $u['estado'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </div>
                            <?php else: ?>
                                <span class="badge-admin badge-active">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/usuarios/editar.php?id=<?= $u['id_usuario'] ?>" class="btn-admin-edit btn-admin-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($u['id_usuario'] !== $_SESSION['admin_id']): ?>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/usuarios/eliminar.php?id=<?= $u['id_usuario'] ?>"
                                        data-delete-name="<?= htmlspecialchars($u['nombre']) ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                                <?php endif; ?>
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
                <p>¿Estás seguro de eliminar al usuario <strong id="deleteItemName"></strong>?</p>
                <p style="font-size:.85rem;color:var(--danger)"><i class="bi bi-exclamation-circle me-1"></i>Esta acción le quitará el acceso permanentemente.</p>
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
