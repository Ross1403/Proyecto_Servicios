<?php
// ============================================================
// admin/logros/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'logros';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$proyecto = filter_input(INPUT_GET, 'proyecto', FILTER_VALIDATE_INT);

$sql = "SELECT l.*, p.nombre AS proyecto_nombre, c.nombre AS cliente_nombre 
        FROM logros l 
        INNER JOIN proyectos p ON l.id_proyecto = p.id_proyecto
        INNER JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (l.titulo LIKE :q OR l.descripcion LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
if ($proyecto) {
    $sql .= " AND l.id_proyecto = :proyecto";
    $params[':proyecto'] = $proyecto;
}
$sql .= " ORDER BY l.id_logro DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logros = $stmt->fetchAll();

// Proyectos para el select
$proyectosList = $db->query("SELECT id_proyecto, nombre FROM proyectos ORDER BY nombre ASC")->fetchAll();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logros e Impacto | Devioz Admin</title>
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
    <span class="topbar-title">Logros</span>
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
            <h1 class="admin-page-title">Gestión de Logros e Impacto</h1>
            <p class="admin-page-subtitle"><?= count($logros) ?> logro(s) encontrado(s)</p>
        </div>
        <a href="/Proyecto_Servicios/admin/logros/crear.php" class="btn-admin-primary">
            <i class="bi bi-plus-lg"></i> Nuevo logro
        </a>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label-admin">Buscar</label>
                    <input type="text" name="q" class="form-control-admin" placeholder="Título o descripción..." value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-admin">Proyecto</label>
                    <select name="proyecto" class="form-select-admin">
                        <option value="">Todos los proyectos</option>
                        <?php foreach ($proyectosList as $p): ?>
                        <option value="<?= $p['id_proyecto'] ?>" <?= $proyecto == $p['id_proyecto'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q || $proyecto): ?>
                    <a href="/Proyecto_Servicios/admin/logros/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($logros)): ?>
            <div class="empty-state"><i class="bi bi-trophy"></i><h5>Sin logros</h5><p>No hay registros que coincidan con la búsqueda.</p></div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Logro / Indicador</th>
                        <th>Proyecto</th>
                        <th>Cliente</th>
                        <th>Mejora (%)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logros as $l): ?>
                    <tr>
                        <td>
                            <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($l['titulo']) ?></p>
                            <div style="font-size:.75rem;color:var(--text-muted);display:flex;gap:.5rem;margin-top:.2rem;">
                                <span>Antes: <?= $l['indicador_anterior'] ?></span>
                                <span><i class="bi bi-arrow-right text-secondary"></i></span>
                                <span>Después: <?= $l['indicador_actual'] ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($l['proyecto_nombre']) ?></td>
                        <td><?= htmlspecialchars($l['cliente_nombre']) ?></td>
                        <td>
                            <?php if ($l['porcentaje_mejora'] > 0): ?>
                                <span style="color:var(--success);font-weight:700;"><i class="bi bi-arrow-up-right me-1"></i><?= $l['porcentaje_mejora'] ?>%</span>
                            <?php elseif ($l['porcentaje_mejora'] < 0): ?>
                                <span style="color:var(--danger);font-weight:700;"><i class="bi bi-arrow-down-right me-1"></i><?= $l['porcentaje_mejora'] ?>%</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-weight:700;">0%</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/logros/editar.php?id=<?= $l['id_logro'] ?>" class="btn-admin-edit btn-admin-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/logros/eliminar.php?id=<?= $l['id_logro'] ?>"
                                        data-delete-name="<?= htmlspecialchars($l['titulo']) ?>">
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
                <p>¿Estás seguro de eliminar el logro <strong id="deleteItemName"></strong>?</p>
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
