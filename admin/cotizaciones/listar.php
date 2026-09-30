<?php
// admin/cotizaciones/listar.php
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'cotizaciones';
$db = getDB();

// Filtros
$q = trim($_GET['q'] ?? '');
$estado = trim($_GET['estado'] ?? '');

$sql = "SELECT * FROM cotizaciones WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (empresa LIKE :q OR nombre LIKE :q OR correo LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
if ($estado !== '') {
    $sql .= " AND estado = :estado";
    $params[':estado'] = $estado;
}
$sql .= " ORDER BY fecha DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$cotizaciones = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Cotizaciones | Devioz Admin</title>
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
    <span class="topbar-title">Cotizaciones</span>
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
            <h1 class="admin-page-title">Cotizaciones Generadas</h1>
            <p class="admin-page-subtitle"><?= count($cotizaciones) ?> cotización(es) encontrada(s)</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label-admin">Buscar</label>
                    <input type="text" name="q" class="form-control-admin" placeholder="Buscar por empresa, nombre o correo..." value="<?= htmlspecialchars($q) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label-admin">Estado</label>
                    <select name="estado" class="form-select-admin">
                        <option value="">Todos los estados</option>
                        <?php foreach (['Pendiente', 'Revisada', 'Contactado', 'Descartada'] as $est): ?>
                        <option value="<?= $est ?>" <?= $estado === $est ? 'selected' : '' ?>><?= $est ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q || $estado): ?>
                    <a href="/Proyecto_Servicios/admin/cotizaciones/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($cotizaciones)): ?>
            <div class="empty-state">
                <i class="bi bi-file-earmark-text"></i>
                <h5>Sin cotizaciones</h5>
                <p>No hay cotizaciones que coincidan con la búsqueda.</p>
            </div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente / Empresa</th>
                        <th>Presupuesto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cotizaciones as $c): ?>
                    <tr>
                        <td><span style="font-weight:700;color:var(--primary-light)">COT-<?= $c['id_cotizacion'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="table-avatar"><?= strtoupper(mb_substr($c['empresa'], 0, 2)) ?></div>
                                <div>
                                    <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($c['empresa']) ?></p>
                                    <p style="margin:0;font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($c['nombre']) ?> — <?= htmlspecialchars($c['correo']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:700;color:var(--success)">$<?= number_format($c['presupuesto_estimado'], 2) ?> USD</td>
                        <td><?= date('d/m/Y', strtotime($c['fecha'])) ?><br><small class="text-muted"><?= date('H:i', strtotime($c['fecha'])) ?></small></td>
                        <td>
                            <select class="form-select-admin estado-select" data-id="<?= $c['id_cotizacion'] ?>" style="width: 140px; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                <?php foreach (['Pendiente', 'Revisada', 'Contactado', 'Descartada'] as $est): ?>
                                <option value="<?= $est ?>" <?= $c['estado'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/cotizaciones/detalles.php?id=<?= $c['id_cotizacion'] ?>" class="btn-admin-edit btn-admin-sm" title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/cotizaciones/eliminar.php?id=<?= $c['id_cotizacion'] ?>"
                                        data-delete-name="Cotización COT-<?= $c['id_cotizacion'] ?>">
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
                <p>¿Estás seguro de eliminar la <strong id="deleteItemName"></strong>?</p>
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
<script>
document.querySelectorAll('.estado-select').forEach(sel => {
    sel.addEventListener('change', async function() {
        const id = this.getAttribute('data-id');
        const estado = this.value;
        try {
            const res = await fetch('/Proyecto_Servicios/admin/cotizaciones/cambiar-estado.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id, estado})
            });
            const data = await res.json();
            if(data.success) {
                showToast('Estado de la cotización actualizado a ' + estado, 'success');
            } else {
                showToast('Error: ' + data.error, 'danger');
            }
        } catch(e) {
            showToast('Error de conexión', 'danger');
        }
    });
});
</script>
</body>
</html>
