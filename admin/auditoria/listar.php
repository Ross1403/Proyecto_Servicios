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
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
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
            <table class="table-admin">
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
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
</body>
</html>
