<?php
// ============================================================
// admin/clientes/listar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'clientes';
$db = getDB();

// Filtros
$q      = trim($_GET['q']      ?? '');
$sector = trim($_GET['sector'] ?? '');

$sql    = "SELECT c.*, (SELECT COUNT(*) FROM proyectos p WHERE p.id_cliente = c.id_cliente) AS total_proyectos FROM clientes c WHERE deleted_at IS NULL";
$params = [];

if ($q !== '') {
    $sql .= " AND c.nombre LIKE :q";
    $params[':q'] = '%' . $q . '%';
}
if ($sector !== '') {
    $sql .= " AND c.sector = :sector";
    $params[':sector'] = $sector;
}
$sql .= " ORDER BY c.destacado DESC, c.nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

$sectores = $db->query("SELECT DISTINCT sector FROM clientes WHERE sector IS NOT NULL ORDER BY sector")->fetchAll(PDO::FETCH_COLUMN);

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Clientes | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
    <style>
        /* Estilizar los controles inyectados por DataTables para que respeten el diseño oscuro */
        div.dataTables_wrapper div.dataTables_length label,
        div.dataTables_wrapper div.dataTables_filter label,
        div.dataTables_wrapper div.dataTables_info,
        div.dataTables_wrapper div.dataTables_paginate {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        div.dataTables_wrapper div.dataTables_length select {
            background-color: var(--admin-bg);
            color: var(--text-primary);
            border: 1px solid var(--admin-border);
            border-radius: var(--radius-sm);
            padding: 0.35rem 1rem; /* Aumentado el padding para hacerlo más ancho */
            min-width: 75px; /* Ancho mínimo garantizado */
            margin: 0 0.5rem;
        }
        div.dataTables_wrapper div.dataTables_filter input {
            background-color: var(--admin-bg);
            color: var(--text-primary);
            border: 1px solid var(--admin-border);
            border-radius: var(--radius-sm);
            padding: 0.35rem 0.75rem;
            margin-left: 0.5rem;
        }
        div.dataTables_wrapper div.dataTables_filter input:focus,
        div.dataTables_wrapper div.dataTables_length select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(108, 99, 255, 0.2);
        }
        /* Ajustar espaciado del wrapper para que no choque con los bordes */
        .dataTables_wrapper {
            padding: 1rem;
        }
        
        /* Estilos de botones de exportación DataTables para que calcen con el tema oscuro */
        div.dt-buttons {
            margin-bottom: 1rem;
        }
        div.dt-buttons .btn {
            background-color: var(--admin-bg);
            color: var(--text-secondary);
            border: 1px solid var(--admin-border);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            margin-right: 0.5rem;
            transition: all 0.2s ease;
        }
        div.dt-buttons .btn:hover {
            background-color: var(--primary-light, #6C63FF);
            color: white;
            border-color: var(--primary-light, #6C63FF);
        }
        
        /* Estilizar la paginación (Anterior, 1, Siguiente) */
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item .page-link {
            background-color: var(--admin-bg);
            color: var(--text-secondary);
            border: 1px solid var(--admin-border);
            margin: 0 2px;
            border-radius: var(--radius-sm);
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item.active .page-link {
            background-color: var(--primary-light, #6C63FF);
            color: #fff;
            border-color: var(--primary-light, #6C63FF);
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item.disabled .page-link {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            border-color: var(--admin-border);
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
    </style>
</head>
<body>
<div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>
<main class="admin-main">
<div class="admin-topbar">
    <button class="topbar-toggle" id="sidebarOpen"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Clientes</span>
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
            <h1 class="admin-page-title">Gestión de Clientes</h1>
            <p class="admin-page-subtitle"><?= count($clientes) ?> cliente(s) encontrado(s)</p>
        </div>
        <a href="/Proyecto_Servicios/admin/clientes/crear.php" class="btn-admin-primary">
            <i class="bi bi-plus-lg"></i> Nuevo cliente
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
                    <label class="form-label-admin">Sector</label>
                    <select name="sector" class="form-select-admin">
                        <option value="">Todos los sectores</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= $sector === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($q || $sector): ?>
                    <a href="/Proyecto_Servicios/admin/clientes/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($clientes)): ?>
            <div class="empty-state"><i class="bi bi-building-slash"></i><h5>Sin clientes</h5><p>No hay clientes que coincidan con la búsqueda.</p></div>
            <?php else: ?>
            <table class="table-admin" id="clientesTable">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Razón Social</th>
                        <th>Sector</th>
                        <th>Proyectos</th>
                        <th>Estado</th>
                        <th>Destacado</th>
                        <th>Relación desde</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($c['logo'] && file_exists('/xampp/htdocs/Proyecto_Servicios/uploads/clientes/' . $c['logo'])): ?>
                                    <img src="/Proyecto_Servicios/uploads/clientes/<?= htmlspecialchars($c['logo']) ?>" class="table-logo" alt="">
                                <?php else: ?>
                                    <div class="table-logo-placeholder"><?= strtoupper(mb_substr($c['nombre'], 0, 2)) ?></div>
                                <?php endif; ?>
                                <div>
                                    <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($c['nombre']) ?></p>
                                    <?php if ($c['web']): ?>
                                    <a href="<?= htmlspecialchars($c['web']) ?>" target="_blank" style="font-size:.75rem;color:var(--primary-light)"><?= htmlspecialchars(parse_url($c['web'], PHP_URL_HOST) ?? $c['web']) ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-size:.85rem;color:var(--text-muted)"><?= htmlspecialchars($c['razon_social'] ?? '—') ?></span></td>
                        <td><?= htmlspecialchars($c['sector'] ?? '—') ?></td>
                        <td style="font-weight:700;color:var(--primary-light)"><?= $c['total_proyectos'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <label class="toggle-switch" title="<?= $c['estado'] ? 'Desactivar' : 'Activar' ?>">
                                    <input type="checkbox"
                                           <?= $c['estado'] ? 'checked' : '' ?>
                                           data-toggle-url="/Proyecto_Servicios/admin/clientes/toggle-estado.php"
                                           data-toggle-id="<?= $c['id_cliente'] ?>"
                                           data-toggle-table="clientes"
                                           data-toggle-field="estado">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="badge-admin <?= $c['estado'] ? 'badge-active' : 'badge-inactive' ?>" data-estado-badge>
                                    <?= $c['estado'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <label class="toggle-switch" title="<?= $c['destacado'] ? 'Quitar de home' : 'Destacar en home' ?>">
                                <input type="checkbox"
                                       <?= $c['destacado'] ? 'checked' : '' ?>
                                       data-toggle-url="/Proyecto_Servicios/admin/clientes/toggle-estado.php"
                                       data-toggle-id="<?= $c['id_cliente'] ?>"
                                       data-toggle-table="clientes"
                                       data-toggle-field="destacado">
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td><?= $c['anio_inicio_relacion'] ? htmlspecialchars($c['anio_inicio_relacion']) : '—' ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/Proyecto_Servicios/admin/clientes/editar.php?id=<?= $c['id_cliente'] ?>" class="btn-admin-edit btn-admin-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn-admin-danger btn-admin-sm"
                                        data-delete-url="/Proyecto_Servicios/admin/clientes/eliminar.php?id=<?= $c['id_cliente'] ?>"
                                        data-delete-name="<?= htmlspecialchars($c['nombre']) ?>">
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
                <p>¿Estás seguro de eliminar al cliente <strong id="deleteItemName"></strong>?</p>
                <p style="font-size:.85rem;color:var(--danger)"><i class="bi bi-exclamation-circle me-1"></i>Esta acción eliminará también todos sus proyectos y logros asociados.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" class="btn-admin-danger" id="confirmDeleteBtn"><i class="bi bi-trash3"></i> Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Scripts para Exportación de DataTables -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
<script>
$(document).ready(function() {
    $('#clientesTable').DataTable({
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "pageLength": 10,
        "ordering": true,
        "info": true,
        "responsive": true,
        // DOM Senior Pro: (l)ength + (B)uttons a la izquierda, (f)ilter a la derecha
        "dom": '<"row mb-3"<"col-sm-12 col-md-8 d-flex align-items-center flex-wrap gap-2"l B><"col-sm-12 col-md-4 d-flex justify-content-end"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-light',
                exportOptions: { columns: [0, 1, 2, 3, 4, 6] }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-light',
                exportOptions: { columns: [0, 1, 2, 3, 4, 6] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer"></i> Imprimir',
                className: 'btn btn-sm btn-outline-light',
                exportOptions: { columns: [0, 1, 2, 3, 4, 6] }
            }
        ],
        "lengthChange": true, // Restaurado: Muestra el texto "Mostrar 10 registros"
        "searching": true     // Restaurado: Muestra el buscador nativo
    });
});
</script>
</body>
</html>
