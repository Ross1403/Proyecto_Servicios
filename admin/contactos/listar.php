<?php
// ============================================================
// admin/contactos/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'contactos';
$db = getDB();

// Filtros
$estado = trim($_GET['estado'] ?? '');

$sql    = "SELECT * FROM contactos WHERE 1=1";
$params = [];

if ($estado !== '') {
    $sql .= " AND estado = :estado";
    $params[':estado'] = $estado;
}
$sql .= " ORDER BY fecha DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$contactos = $stmt->fetchAll();

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
    <title>Mensajes de Contacto | Devioz Admin</title>
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
    <span class="topbar-title">Bandeja de Contacto</span>
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
            <h1 class="admin-page-title">Bandeja de Entrada</h1>
            <p class="admin-page-subtitle"><?= count($contactos) ?> mensaje(s)</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label-admin">Estado del mensaje</label>
                    <select name="estado" class="form-select-admin">
                        <option value="">Todos los mensajes</option>
                        <?php foreach ($estados as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>" <?= $estado === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if ($estado): ?>
                    <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($contactos)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><h5>Bandeja vacía</h5><p>No hay mensajes por el momento.</p></div>
            <?php else: ?>
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Remitente</th>
                        <th>Servicio de interés</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactos as $c): 
                        $badgeCls = match($c['estado']) {
                            'Revisado'   => 'badge-revisado',
                            'Contactado' => 'badge-contactado',
                            'Cerrado'    => 'badge-cerrado',
                            default      => 'badge-nuevo'
                        };
                    ?>
                    <tr>
                        <td>
                            <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($c['nombre']) ?></p>
                            <p style="margin:0;font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($c['correo']) ?></p>
                        </td>
                        <td><?= htmlspecialchars($c['servicio'] ?? '—') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
                        <td>
                            <form action="/Proyecto_Servicios/admin/contactos/cambiar-estado.php" method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $c['id_contacto'] ?>">
                                <select name="nuevo_estado" class="form-select-admin" style="padding:.3rem .5rem; font-size:.75rem; width:110px; display:inline-block;" onchange="this.form.submit()">
                                    <?php foreach ($estados as $e): ?>
                                    <option value="<?= htmlspecialchars($e) ?>" <?= $c['estado'] === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/contactos/ver.php?id=<?= $c['id_contacto'] ?>" class="btn-admin-primary btn-admin-sm" title="Ver mensaje">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/contactos/eliminar.php?id=<?= $c['id_contacto'] ?>"
                                        data-delete-name="Mensaje de <?= htmlspecialchars($c['nombre']) ?>">
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
                <p>¿Estás seguro de eliminar el <strong id="deleteItemName"></strong>?</p>
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
