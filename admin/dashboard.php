<?php
// ============================================================
// admin/dashboard.php — Panel principal con estadísticas reales
// ============================================================
session_start();
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$db = getDB();

// ---- Conteos reales ----
$totalClientes      = $db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$clientesActivos    = $db->query("SELECT COUNT(*) FROM clientes WHERE estado = 1")->fetchColumn();
$totalProyectos     = $db->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$proyectosFinalizados = $db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'Finalizado'")->fetchColumn();
$proyectosEnDesarrollo = $db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'En desarrollo'")->fetchColumn();
$casosExito         = $db->query("SELECT COUNT(*) FROM proyectos WHERE destacado = 1")->fetchColumn();
$totalServicios     = $db->query("SELECT COUNT(*) FROM servicios WHERE estado = 1")->fetchColumn();
$mensajesNuevos     = $db->query("SELECT COUNT(*) FROM contactos WHERE estado = 'Nuevo'")->fetchColumn();
$totalLogros        = $db->query("SELECT COUNT(*) FROM logros")->fetchColumn();

// ---- Datos para gráfico: proyectos por cliente ----
$chartData = $db->query("
    SELECT c.nombre AS cliente, COUNT(p.id_proyecto) AS total
    FROM clientes c
    INNER JOIN proyectos p ON p.id_cliente = c.id_cliente
    GROUP BY c.id_cliente, c.nombre
    ORDER BY total DESC
    LIMIT 8
")->fetchAll();

$chartLabels = array_map(fn($r) => $r['cliente'], $chartData);
$chartValues = array_map(fn($r) => (int)$r['total'], $chartData);

// ---- Últimos contactos ----
$ultimosContactos = $db->query("
    SELECT * FROM contactos ORDER BY fecha DESC LIMIT 5
")->fetchAll();

// ---- Proyectos recientes ----
$proyectosRecientes = $db->query("
    SELECT p.nombre, p.estado, p.id_proyecto, c.nombre AS cliente
    FROM proyectos p INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    ORDER BY p.id_proyecto DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $pageTitle ?> | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <!-- Overlay móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <main class="admin-main">
        <!-- Topbar -->
        <div class="admin-topbar">
            <button class="topbar-toggle" id="sidebarOpen" aria-label="Abrir menú">
                <i class="bi bi-list"></i>
            </button>
            <span class="topbar-title">Dashboard</span>
            <div class="topbar-actions">
                <?php if ($mensajesNuevos > 0): ?>
                <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="topbar-btn">
                    <i class="bi bi-envelope-fill"></i>
                    <span class="badge bg-danger rounded-pill"><?= $mensajesNuevos ?></span>
                </a>
                <?php endif; ?>
                <a href="/Proyecto_Servicios/index.php" target="_blank" class="topbar-btn">
                    <i class="bi bi-box-arrow-up-right"></i> Ver sitio
                </a>
            </div>
        </div>

        <div class="admin-page">
            <!-- Encabezado -->
            <div class="admin-page-header">
                <div>
                    <h1 class="admin-page-title">Bienvenido, <?= htmlspecialchars($_SESSION['admin_nombre']) ?> 👋</h1>
                    <p class="admin-page-subtitle">Panel de control — <?= date('d \d\e F \d\e Y') ?></p>
                </div>
                <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="btn-admin-primary">
                    <i class="bi bi-envelope-fill"></i>
                    <?php if ($mensajesNuevos > 0): ?>
                        <?= $mensajesNuevos ?> mensaje(s) nuevo(s)
                    <?php else: ?>
                        Bandeja de mensajes
                    <?php endif; ?>
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card sc-purple">
                        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalClientes ?></div>
                            <div class="stat-card-label">Clientes totales</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-check-circle-fill"></i> <?= $clientesActivos ?> activos
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card sc-blue">
                        <div class="stat-card-icon"><i class="bi bi-kanban-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalProyectos ?></div>
                            <div class="stat-card-label">Proyectos totales</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-arrow-up"></i> <?= $proyectosEnDesarrollo ?> en desarrollo
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card sc-green">
                        <div class="stat-card-icon"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $proyectosFinalizados ?></div>
                            <div class="stat-card-label">Proyectos finalizados</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-stars"></i> <?= $casosExito ?> casos de éxito
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card <?= $mensajesNuevos > 0 ? 'sc-red' : 'sc-yellow' ?>">
                        <div class="stat-card-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $mensajesNuevos ?></div>
                            <div class="stat-card-label">Mensajes nuevos</div>
                            <div class="stat-card-trend">
                                <i class="bi bi-trophy-fill" style="color:var(--warning)"></i> <?= $totalLogros ?> logros registrados
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda fila de stats -->
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="stat-card sc-green">
                        <div class="stat-card-icon"><i class="bi bi-gear-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalServicios ?></div>
                            <div class="stat-card-label">Servicios activos</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card sc-blue">
                        <div class="stat-card-icon"><i class="bi bi-trophy-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalLogros ?></div>
                            <div class="stat-card-label">Logros totales</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat-card sc-purple">
                        <div class="stat-card-icon"><i class="bi bi-stars"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $casosExito ?></div>
                            <div class="stat-card-label">Casos de éxito</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico y últimos contactos -->
            <div class="row g-4 mb-4">
                <!-- Gráfico Chart.js -->
                <div class="col-lg-7">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-bar-chart-line me-2"></i>Proyectos por cliente</h2>
                        </div>
                        <div class="admin-card-body">
                            <div class="chart-container" style="height:260px">
                                <canvas id="chartProyectos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos mensajes -->
                <div class="col-lg-5">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-envelope me-2"></i>Últimos mensajes</h2>
                            <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="btn-admin-secondary btn-admin-sm">Ver todos</a>
                        </div>
                        <div style="overflow:hidden">
                            <?php if (empty($ultimosContactos)): ?>
                            <div class="empty-state"><i class="bi bi-inbox"></i><p>Sin mensajes aún</p></div>
                            <?php else: ?>
                            <?php foreach ($ultimosContactos as $msg):
                                $badgeCls = match($msg['estado']) {
                                    'Revisado'   => 'badge-revisado',
                                    'Contactado' => 'badge-contactado',
                                    'Cerrado'    => 'badge-cerrado',
                                    default      => 'badge-nuevo'
                                };
                            ?>
                            <div style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--admin-border)">
                                <div class="table-avatar" style="width:32px;height:32px;font-size:.75rem">
                                    <?= strtoupper(substr($msg['nombre'], 0, 1)) ?>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <p style="margin:0;font-size:.82rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <?= htmlspecialchars($msg['nombre']) ?>
                                    </p>
                                    <p style="margin:0;font-size:.75rem;color:var(--text-muted)">
                                        <?= htmlspecialchars(mb_strimwidth($msg['mensaje'], 0, 40, '…')) ?>
                                    </p>
                                </div>
                                <span class="badge-admin <?= $badgeCls ?>"><?= $msg['estado'] ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proyectos recientes -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="bi bi-kanban me-2"></i>Proyectos recientes</h2>
                    <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-secondary btn-admin-sm">Ver todos</a>
                </div>
                <div style="overflow-x:auto">
                    <table class="table-admin">
                        <thead>
                            <tr>
                                <th>Proyecto</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Registrado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proyectosRecientes as $pr):
                                $pCls = match($pr['estado']) {
                                    'Finalizado'    => 'badge-fin',
                                    'En desarrollo' => 'badge-dev',
                                    'Planificado'   => 'badge-plan',
                                    'Suspendido'    => 'badge-sus',
                                    default         => 'badge-plan'
                                };
                            ?>
                            <tr>
                                <td style="color:var(--text-primary);font-weight:600"><?= htmlspecialchars($pr['nombre']) ?></td>
                                <td><?= htmlspecialchars($pr['cliente']) ?></td>
                                <td><span class="badge-admin <?= $pCls ?>"><?= $pr['estado'] ?></span></td>
                                <td>#<?= $pr['id_proyecto'] ?></td>
                                <td>
                                    <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-edit btn-admin-sm">
                                        <i class="bi bi-pencil"></i> Gestionar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="row g-3 mt-4">
                <div class="col-12">
                    <h3 style="font-size:.85rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:1rem">
                        Accesos rápidos
                    </h3>
                </div>
                <?php
                $accesos = [
                    ['/Proyecto_Servicios/admin/clientes/crear.php',  'bi-building-add',  'Nuevo cliente',   'sc-purple'],
                    ['/Proyecto_Servicios/admin/proyectos/crear.php',  'bi-kanban',        'Nuevo proyecto',  'sc-blue'],
                    ['/Proyecto_Servicios/admin/logros/crear.php',     'bi-trophy',        'Nuevo logro',     'sc-green'],
                    ['/Proyecto_Servicios/admin/servicios/crear.php',  'bi-gear-wide',     'Nuevo servicio',  'sc-yellow'],
                    ['/Proyecto_Servicios/admin/contactos/listar.php', 'bi-envelope',      'Ver mensajes',    'sc-red'],
                    ['/Proyecto_Servicios/admin/usuarios/listar.php',  'bi-person-plus',   'Gestionar usuarios', 'sc-blue'],
                ];
                foreach ($accesos as $a): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?= $a[0] ?>" class="stat-card <?= $a[3] ?> d-flex flex-column align-items-center text-center" style="padding:1.25rem;gap:.5rem;text-decoration:none">
                        <div class="stat-card-icon" style="margin:0"><i class="bi <?= $a[1] ?>"></i></div>
                        <span style="font-size:.8rem;font-weight:600;color:var(--text-secondary)"><?= $a[2] ?></span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div><!-- /admin-page -->
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
<script>
// Chart.js — Proyectos por cliente
const ctx = document.getElementById('chartProyectos').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Proyectos',
            data: <?= json_encode($chartValues) ?>,
            backgroundColor: [
                'rgba(108,99,255,.7)', 'rgba(255,107,107,.7)', 'rgba(0,212,170,.7)',
                'rgba(255,176,32,.7)', 'rgba(56,189,248,.7)',  'rgba(108,99,255,.5)',
                'rgba(255,107,107,.5)', 'rgba(0,212,170,.5)'
            ],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1A1A2E',
                borderColor: 'rgba(108,99,255,.3)',
                borderWidth: 1,
                titleColor: '#F0F0F5',
                bodyColor: '#A0A0B8',
                padding: 10,
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#6B6B85', font: { size: 11, family: 'Inter' }, maxRotation: 30 }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,.05)' },
                ticks: { color: '#6B6B85', font: { size: 11, family: 'Inter' }, stepSize: 1 }
            }
        }
    }
});
</script>
</body>
</html>
