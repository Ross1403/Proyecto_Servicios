<?php
// admin/auditoria/listar.php
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$activePage = 'auditoria';
$db = getDB();

// Filtros
$modulo = trim($_GET['modulo'] ?? '');

$sql = "SELECT a.*, u.nombre AS usuario_nombre, u.correo AS usuario_correo 
        FROM audit_logs a 
        LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario 
        WHERE 1=1";
$params = [];

if ($modulo !== '') {
    $sql .= " AND a.modulo = :modulo";
    $params[':modulo'] = $modulo;
}
$sql .= " ORDER BY a.fecha DESC LIMIT 100"; // Top 100 por rendimiento

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Obtener módulos únicos para el filtro
$modulosUnicos = $db->query("SELECT DISTINCT modulo FROM audit_logs ORDER BY modulo")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Auditoría | Devioz Admin</title>
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
            padding: 0.35rem 1rem;
            min-width: 75px;
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
        .dataTables_wrapper { padding: 1rem; }
        
        div.dt-buttons { margin-bottom: 1rem; }
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
    <span class="topbar-title">Auditoría (Audit Logs)</span>
</div>
<div class="admin-page">

    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">Registro de Auditoría</h1>
            <p class="admin-page-subtitle">Monitoreo de trazabilidad y eventos del sistema (Últimos 100 registros)</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="admin-card mb-4">
        <div class="admin-card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label-admin">Módulo</label>
                    <select name="modulo" class="form-select-admin">
                        <option value="">Todos los módulos</option>
                        <?php foreach ($modulosUnicos as $mod): ?>
                        <option value="<?= htmlspecialchars($mod) ?>" <?= $modulo === $mod ? 'selected' : '' ?>><?= htmlspecialchars($mod) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-fill" style="justify-content:center">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if ($modulo): ?>
                    <a href="/Proyecto_Servicios/admin/auditoria/listar.php" class="btn-admin-secondary" style="padding:.6rem .8rem">
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
            <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="bi bi-shield-check"></i>
                <h5>Sin registros</h5>
                <p>No hay eventos de auditoría para mostrar.</p>
            </div>
            <?php else: ?>
            <table class="table-admin" id="auditoriaTable">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario / IP</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="font-size:0.8rem">
                            <?= date('d/m/Y', strtotime($log['fecha'])) ?><br>
                            <span class="text-muted"><?= date('H:i:s', strtotime($log['fecha'])) ?></span>
                        </td>
                        <td>
                            <?php if ($log['usuario_nombre']): ?>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($log['usuario_nombre']) ?></p>
                                <p style="margin:0;font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($log['ip']) ?></p>
                            <?php else: ?>
                                <p style="margin:0;font-size:.875rem;font-weight:600;color:var(--text-primary)">Sistema/Anónimo</p>
                                <p style="margin:0;font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($log['ip']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge" style="background:rgba(108,99,255,0.1);color:var(--primary);padding:0.4rem 0.8rem;border-radius:20px"><?= htmlspecialchars($log['modulo']) ?></span></td>
                        <td style="font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($log['accion']) ?></td>
                        <td style="font-size:0.8rem;color:var(--text-secondary);max-width:300px;word-wrap:break-word;">
                            <?= htmlspecialchars($log['detalles']) ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery ya debería estar en los archivos o lo aseguramos -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
    $('#auditoriaTable').DataTable({
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
        "dom": '<"row mb-3"<"col-sm-12 col-md-8 d-flex align-items-center flex-wrap gap-2"l B><"col-sm-12 col-md-4 d-flex justify-content-end"f>>rt<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        "buttons": [
            { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-outline-light' },
            { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf"></i> PDF', className: 'btn btn-sm btn-outline-light' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> Imprimir', className: 'btn btn-sm btn-outline-light' }
        ],
        "lengthChange": true,
        "searching": true
    });
});
</script>
</body>
</html>
