<?php
// ============================================================
// admin/proyectos/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'proyectos';
$db = getDB();

// Filtros
$q      = trim($_GET['q'] ?? '');
$estado = trim($_GET['estado'] ?? '');

$sql    = "SELECT p.*, c.nombre AS cliente_nombre, 
           (SELECT COUNT(*) FROM logros l WHERE l.id_proyecto = p.id_proyecto) AS total_logros 
           FROM proyectos p 
           INNER JOIN clientes c ON p.id_cliente = c.id_cliente 
           WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND p.nombre LIKE :q";
    $params[':q'] = '%' . $q . '%';
}
if ($estado !== '') {
    $sql .= " AND p.estado = :estado";
    $params[':estado'] = $estado;
}
$sql .= " ORDER BY p.destacado DESC, p.id_proyecto DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$proyectos = $stmt->fetchAll();

$estados = ['Planificado', 'En desarrollo', 'Finalizado', 'Suspendido'];

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Proyectos | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
    <span class="topbar-title">Proyectos</span>
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
            <h1 class="admin-page-title">Gestión de Proyectos</h1>
            <p class="admin-page-subtitle"><?= count($proyectos) ?> proyecto(s) encontrado(s)</p>
        </div>
        <a href="/Proyecto_Servicios/admin/proyectos/crear.php" class="btn-admin-primary">
            <i class="bi bi-plus-lg"></i> Nuevo proyecto
        </a>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label-admin">Buscar</label>
                    <input type="text" name="q" class="form-control-admin" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-admin">Estado</label>
                    <select name="estado" class="form-select-admin">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>" <?= $estado === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q || $estado): ?>
                    <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($proyectos)): ?>
            <div class="empty-state"><i class="bi bi-kanban"></i><h5>Sin proyectos</h5><p>No hay proyectos que coincidan con la búsqueda.</p></div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Proyecto</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Logros</th>
                        <th>Destacado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proyectos as $p): 
                        $pCls = match($p['estado']) {
                            'Finalizado'    => 'badge-fin',
                            'En desarrollo' => 'badge-dev',
                            'Planificado'   => 'badge-plan',
                            'Suspendido'    => 'badge-sus',
                            default         => 'badge-plan'
                        };
                    ?>
                    <tr>
                        <td>
                            <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($p['nombre']) ?></p>
                            <p style="margin:0;font-size:.75rem;color:var(--text-muted)"><?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?></p>
                        </td>
                        <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                        <td><span class="badge-admin <?= $pCls ?>"><?= $p['estado'] ?></span></td>
                        <td style="font-weight:700;color:var(--primary-light)"><?= $p['total_logros'] ?></td>
                        <td>
                            <label class="toggle-switch" title="<?= $p['destacado'] ? 'Quitar destacado' : 'Hacer destacado' ?>">
                                <input type="checkbox"
                                       <?= $p['destacado'] ? 'checked' : '' ?>
                                       data-toggle-url="/Proyecto_Servicios/admin/clientes/toggle-estado.php"
                                       data-toggle-id="<?= $p['id_proyecto'] ?>"
                                       data-toggle-table="proyectos"
                                       data-toggle-field="destacado">
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/proyectos/editar.php?id=<?= $p['id_proyecto'] ?>" class="btn-admin-edit btn-admin-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/proyectos/eliminar.php?id=<?= $p['id_proyecto'] ?>"
                                        data-delete-name="<?= htmlspecialchars($p['nombre']) ?>">
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
                <p>¿Estás seguro de eliminar el proyecto <strong id="deleteItemName"></strong>?</p>
                <p style="font-size:.85rem;color:var(--danger)"><i class="bi bi-exclamation-circle me-1"></i>Esta acción eliminará también todos sus logros asociados.</p>
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
