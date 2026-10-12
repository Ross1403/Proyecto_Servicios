<?php
// ============================================================
// admin/dashboard.php — Panel principal con estadísticas reales (Senior Pro Ultra Responsive)
// ============================================================
session_start();
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$db = getDB();

// ---- Conteos reales ----
$totalClientes         = (int)$db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$clientesActivos       = (int)$db->query("SELECT COUNT(*) FROM clientes WHERE estado = 1")->fetchColumn();
$totalProyectos        = (int)$db->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$proyectosFinalizados  = (int)$db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'Finalizado'")->fetchColumn();
$proyectosEnDesarrollo = (int)$db->query("SELECT COUNT(*) FROM proyectos WHERE estado = 'En desarrollo'")->fetchColumn();
$casosExito            = (int)$db->query("SELECT COUNT(*) FROM proyectos WHERE destacado = 1")->fetchColumn();
$totalServicios        = (int)$db->query("SELECT COUNT(*) FROM servicios WHERE estado = 1")->fetchColumn();
$mensajesNuevos        = (int)$db->query("SELECT COUNT(*) FROM contactos WHERE estado = 'Nuevo'")->fetchColumn();
$totalLogros           = (int)$db->query("SELECT COUNT(*) FROM logros")->fetchColumn();

// ---- Métricas del Monitor de Avances y Entregables ZIP ----
$proyectosConZip   = (int)$db->query("SELECT COUNT(*) FROM proyectos WHERE archivo_zip IS NOT NULL AND archivo_zip != ''")->fetchColumn();
$promedioAvance    = (int)round((float)$db->query("SELECT AVG(porcentaje_avance) FROM proyectos")->fetchColumn() ?: 0);
$proyectosListos   = (int)$db->query("SELECT COUNT(*) FROM proyectos WHERE porcentaje_avance = 100")->fetchColumn();

// ---- Métricas del Módulo de Cotizaciones y Pipeline ----
$totalCotizaciones      = (int)$db->query("SELECT COUNT(*) FROM cotizaciones")->fetchColumn();
$cotizacionesPendientes = (int)$db->query("SELECT COUNT(*) FROM cotizaciones WHERE estado = 'Pendiente'")->fetchColumn();
$cotizacionesAprobadas  = (int)$db->query("SELECT COUNT(*) FROM cotizaciones WHERE estado IN ('Revisada', 'Contactado')")->fetchColumn();
$volumenEstimado        = (float)$db->query("SELECT SUM(presupuesto_estimado) FROM cotizaciones WHERE estado != 'Descartada'")->fetchColumn() ?: 0.0;
$ultimasCotizaciones    = $db->query("SELECT id_cotizacion, nombre, empresa, presupuesto_estimado, estado, fecha FROM cotizaciones ORDER BY id_cotizacion DESC LIMIT 5")->fetchAll();

// ---- Datos para gráfico: proyectos por cliente ----
$chartData = $db->query("
    SELECT c.nombre AS cliente, COUNT(p.id_proyecto) AS total
    FROM clientes c
    INNER JOIN proyectos p ON p.id_cliente = c.id_cliente
    GROUP BY c.id_cliente, c.nombre
    ORDER BY total DESC
    LIMIT 6
")->fetchAll();

$chartLabels = array_map(fn($r) => $r['cliente'], $chartData);
$chartValues = array_map(fn($r) => (int)$r['total'], $chartData);

// ---- Datos para gráfico: clientes por sector (Business Intelligence) ----
$sectorData = $db->query("
    SELECT sector, COUNT(id_cliente) AS total
    FROM clientes
    WHERE sector IS NOT NULL AND sector != ''
    GROUP BY sector
    ORDER BY total DESC
    LIMIT 6
")->fetchAll();

$sectorLabels = array_map(fn($r) => $r['sector'], $sectorData);
$sectorValues = array_map(fn($r) => (int)$r['total'], $sectorData);

// ---- Últimos contactos ----
$ultimosContactos = $db->query("
    SELECT * FROM contactos ORDER BY fecha DESC LIMIT 5
")->fetchAll();

// ---- Proyectos recientes con datos de avance y ZIP ----
$proyectosRecientes = $db->query("
    SELECT p.nombre, p.estado, p.id_proyecto, p.porcentaje_avance, p.archivo_zip, p.archivo_zip_nombre, c.nombre AS cliente
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
    <style>
        /* Full Width Ultra-Wide Dashboard (Sin márgenes vacíos a los lados) */
        .admin-page { 
            width: 100% !important; 
            max-width: 100% !important; 
            margin: 0 !important; 
            padding: 1.5rem 2rem !important; 
        }
                .live-dot {
            width: 7px; height: 7px; border-radius: 50%; background: #00D4AA;
            box-shadow: 0 0 8px #00D4AA; animation: pulseLive 1.5s infinite; display: inline-block;
        }
        @keyframes pulseLive {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 212, 170, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(0, 212, 170, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 212, 170, 0); }
        }
        .stat-card { height: 100%; min-height: 105px; }
        .table-admin th, .table-admin td { vertical-align: middle; }
        .chart-container { position: relative; width: 100%; }
        @media (max-width: 768px) {
            .admin-page { padding: 1rem !important; }
            .stat-card-value { font-size: 1.4rem; }
            .admin-page-title { font-size: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
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
                <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="topbar-btn" title="<?= $mensajesNuevos ?> mensajes nuevos">
                    <i class="bi bi-envelope-fill"></i>
                    <span class="badge bg-danger rounded-pill"><?= $mensajesNuevos ?></span>
                </a>
                <?php endif; ?>
                <a href="/Proyecto_Servicios/index.php" target="_blank" class="topbar-btn">
                    <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-sm-inline">Ver sitio</span>
                </a>
            </div>
        </div>

        <div class="admin-page">
            <!-- Encabezado Principal -->
            <div class="admin-page-header flex-wrap gap-3">
                <div>
                    <h1 class="admin-page-title">Bienvenido, <?= htmlspecialchars($_SESSION['admin_nombre']) ?> 👋</h1>
                    <p class="admin-page-subtitle">Panel de Control & Inteligencia Operativa — <?= date('d \d\e F \d\e Y') ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/Proyecto_Servicios/admin/proyectos/crear.php" class="btn-admin-primary">
                        <i class="bi bi-plus-circle-fill"></i> <span class="d-none d-sm-inline">Nuevo Proyecto</span>
                    </a>
                    <a href="/Proyecto_Servicios/admin/cotizaciones/listar.php" class="btn-admin-secondary">
                        <i class="bi bi-receipt"></i> <span class="d-none d-sm-inline">Cotizaciones</span>
                    </a>
                </div>
            </div>

            <!-- FILA 1: TARJETAS DE MÉTRICAS CLAVE (4 Columnas Equilibradas) -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card sc-purple">
                        <div class="stat-card-icon"><i class="bi bi-building"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalClientes ?></div>
                            <div class="stat-card-label">Clientes Totales</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-check-circle-fill"></i> <?= $clientesActivos ?> activos
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card sc-blue">
                        <div class="stat-card-icon"><i class="bi bi-kanban-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $totalProyectos ?></div>
                            <div class="stat-card-label">Proyectos Totales</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-arrow-up"></i> <?= $proyectosEnDesarrollo ?> en desarrollo
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card sc-green">
                        <div class="stat-card-icon"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $proyectosFinalizados ?></div>
                            <div class="stat-card-label">Proyectos Finalizados</div>
                            <div class="stat-card-trend trend-up">
                                <i class="bi bi-stars"></i> <?= $casosExito ?> casos de éxito
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card <?= $mensajesNuevos > 0 ? 'sc-red' : 'sc-yellow' ?>">
                        <div class="stat-card-icon"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <div class="stat-card-value"><?= $mensajesNuevos ?></div>
                            <div class="stat-card-label">Mensajes Nuevos</div>
                            <div class="stat-card-trend">
                                <i class="bi bi-gear-fill"></i> <?= $totalServicios ?> servicios activos
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 2: MONITOR DE HITOS / ZIP & PIPELINE DE COTIZACIONES -->
            <div class="row g-4 mb-4">
                <!-- Monitor de Entregables & Avances -->
                <div class="col-12 col-lg-5">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-box-seam-fill text-warning me-2"></i>Entregables & Avances</h2>
                            <span class="badge-admin badge-dev" style="font-size:0.7rem">Hitos Devioz</span>
                        </div>
                        <div class="admin-card-body d-flex flex-column justify-content-between">
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="p-3 rounded h-100" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25)">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="bi bi-file-earmark-zip-fill text-warning fs-5"></i>
                                            <span style="font-size:0.72rem;color:var(--text-muted);font-weight:700">PAQUETES ZIP</span>
                                        </div>
                                        <div style="font-size:1.6rem;font-weight:800;color:var(--text-primary)"><?= $proyectosConZip ?></div>
                                        <small style="color:var(--text-muted);font-size:0.72rem">con archivos descargables</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded h-100" style="background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.25)">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="bi bi-speedometer2 text-info fs-5"></i>
                                            <span style="font-size:0.72rem;color:var(--text-muted);font-weight:700">AVANCE GLOBAL</span>
                                        </div>
                                        <div style="font-size:1.6rem;font-weight:800;color:var(--text-primary)"><?= $promedioAvance ?>%</div>
                                        <small style="color:var(--text-muted);font-size:0.72rem">promedio de progreso</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-3 rounded" style="background:rgba(255,255,255,0.02);border:1px solid var(--admin-border)">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span style="font-size:0.8rem;font-weight:600;color:var(--text-secondary)">
                                        <i class="bi bi-check-all text-success me-1"></i> Proyectos al 100% de Entrega
                                    </span>
                                    <span class="badge-admin badge-fin"><?= $proyectosListos ?> completados</span>
                                </div>
                                <div class="progress mt-2" style="height:6px;background:rgba(255,255,255,0.08);border-radius:4px;overflow:hidden">
                                    <div class="progress-bar bg-success" style="width: <?= $totalProyectos > 0 ? round(($proyectosListos / $totalProyectos) * 100) : 0 ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pipeline Comercial de Cotizaciones en Tiempo Real -->
                <div class="col-12 col-lg-7">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="admin-card-title mb-0"><i class="bi bi-cash-stack text-success me-2"></i>Pipeline de Cotizaciones</h2>
                                <span class="badge-admin badge-fin d-flex align-items-center gap-1" style="font-size:0.68rem;padding:0.15rem 0.5rem">
                                    <span class="live-dot"></span> EN VIVO
                                </span>
                            </div>
                            <a href="/Proyecto_Servicios/admin/cotizaciones/listar.php" class="btn-admin-secondary btn-admin-sm">Ver todas</a>
                        </div>
                        <div class="admin-card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.03);border:1px solid var(--admin-border)">
                                        <div id="statTotalCot" style="font-size:1.25rem;font-weight:800;color:var(--primary-light)"><?= $totalCotizaciones ?></div>
                                        <small style="font-size:0.7rem;color:var(--text-muted)">Total Solicitudes</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25)">
                                        <div id="statPendCot" style="font-size:1.25rem;font-weight:800;color:#f59e0b"><?= $cotizacionesPendientes ?></div>
                                        <small style="font-size:0.7rem;color:var(--text-muted)">Pendientes</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded text-center" style="background:rgba(0,212,170,0.08);border:1px solid rgba(0,212,170,0.25)">
                                        <div id="statVolumenCot" style="font-size:1.15rem;font-weight:800;color:var(--success)">S/ <?= number_format($volumenEstimado, 2) ?></div>
                                        <small style="font-size:0.7rem;color:var(--text-muted)">Volumen Estimado</small>
                                    </div>
                                </div>
                            </div>

                            <div style="overflow-x:auto">
                                <table class="table-admin mb-0" style="font-size:0.8rem">
                                    <thead>
                                        <tr>
                                            <th>Cliente / Empresa</th>
                                            <th>Presupuesto</th>
                                            <th>Estado</th>
                                            <th style="min-width:140px"><i class="bi bi-clock-history me-1"></i>Fecha & Hora</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cotizacionesRealtimeBody">
                                        <?php if (empty($ultimasCotizaciones)): ?>
                                        <tr><td colspan="4" class="text-center py-2 text-muted">Sin cotizaciones registradas aún.</td></tr>
                                        <?php else: ?>
                                        <?php foreach ($ultimasCotizaciones as $cot): 
                                            $cotBadge = match($cot['estado']) {
                                                'Pendiente'  => 'badge-nuevo',
                                                'Revisada'   => 'badge-revisado',
                                                'Contactado' => 'badge-contactado',
                                                'Descartada' => 'badge-sus',
                                                default      => 'badge-nuevo'
                                            };
                                            $timestamp = strtotime($cot['fecha']);
                                            $fechaFmt = date('d/m/Y', $timestamp);
                                            $horaFmt  = date('h:i A', $timestamp);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="fw-semibold text-white"><?= htmlspecialchars($cot['nombre']) ?></span>
                                                <?php if (!empty($cot['empresa'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($cot['empresa']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold text-success">
                                                <?= $cot['presupuesto_estimado'] ? 'S/ ' . number_format($cot['presupuesto_estimado'], 2) : 'A definir' ?>
                                            </td>
                                            <td><span class="badge-admin <?= $cotBadge ?>"><?= $cot['estado'] ?></span></td>
                                            <td>
                                                <div style="color:var(--text-primary);font-weight:600"><?= $fechaFmt ?></div>
                                                <small style="color:var(--text-muted);font-size:0.72rem"><i class="bi bi-clock me-1"></i><?= $horaFmt ?></small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 3: PROYECTOS RECIENTES & MENSAJES (Estructura 7/5 perfectamente alineada) -->
            <div class="row g-4 mb-4">
                <!-- Proyectos recientes con Avance y ZIP -->
                <div class="col-12 col-lg-7">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-kanban me-2"></i>Proyectos Recientes</h2>
                            <a href="/Proyecto_Servicios/admin/proyectos/listar.php" class="btn-admin-secondary btn-admin-sm">Ver todos</a>
                        </div>
                        <div style="overflow-x:auto">
                            <table class="table-admin mb-0">
                                <thead>
                                    <tr>
                                        <th>Proyecto</th>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Avance / ZIP</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($proyectosRecientes)): ?>
                                    <tr><td colspan="5" class="text-center py-3 text-muted">Sin proyectos registrados.</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($proyectosRecientes as $pr):
                                        $pCls = match($pr['estado']) {
                                            'Finalizado'    => 'badge-fin',
                                            'En desarrollo' => 'badge-dev',
                                            'Planificado'   => 'badge-plan',
                                            'Suspendido'    => 'badge-sus',
                                            default         => 'badge-plan'
                                        };
                                        $pctPr = (int)($pr['porcentaje_avance'] ?? 0);
                                        $hasZipPr = !empty($pr['archivo_zip']) && file_exists(__DIR__ . '/../../uploads/proyectos/entregables/' . $pr['archivo_zip']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div style="color:var(--text-primary);font-weight:600"><?= htmlspecialchars($pr['nombre']) ?></div>
                                            <small style="color:var(--text-muted);font-size:0.72rem">ID #<?= $pr['id_proyecto'] ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($pr['cliente']) ?></td>
                                        <td><span class="badge-admin <?= $pCls ?>"><?= $pr['estado'] ?></span></td>
                                        <td>
                                            <div style="min-width:120px">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span style="font-size:0.75rem;font-weight:700;color:var(--text-primary)"><?= $pctPr ?>%</span>
                                                    <?php if ($hasZipPr): ?>
                                                    <a href="/Proyecto_Servicios/uploads/proyectos/entregables/<?= urlencode($pr['archivo_zip']) ?>" download="<?= htmlspecialchars($pr['archivo_zip_nombre'] ?: $pr['archivo_zip']) ?>" class="badge-admin badge-dev text-decoration-none" style="padding:0.15rem 0.45rem;font-size:0.68rem" title="Descargar ZIP">
                                                        <i class="bi bi-file-earmark-zip-fill text-warning me-1"></i>ZIP
                                                    </a>
                                                    <?php else: ?>
                                                    <span style="font-size:0.68rem;color:var(--text-muted)">Sin ZIP</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="progress" style="height:5px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden">
                                                    <div class="progress-bar" style="width:<?= $pctPr ?>%;background:<?= $pctPr == 100 ? 'var(--success)' : 'linear-gradient(90deg, var(--primary), #38bdf8)' ?>;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="/Proyecto_Servicios/admin/proyectos/editar.php?id=<?= $pr['id_proyecto'] ?>" class="btn-admin-edit btn-admin-sm" title="Editar proyecto">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Últimos Mensajes Recibidos -->
                <div class="col-12 col-lg-5">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-envelope me-2"></i>Últimos Mensajes</h2>
                            <a href="/Proyecto_Servicios/admin/contactos/listar.php" class="btn-admin-secondary btn-admin-sm">Ver todos</a>
                        </div>
                        <div style="overflow:hidden">
                            <?php if (empty($ultimosContactos)): ?>
                            <div class="empty-state py-4"><i class="bi bi-inbox fs-2"></i><p class="mb-0 mt-2">Sin mensajes aún</p></div>
                            <?php else: ?>
                            <?php foreach ($ultimosContactos as $msg):
                                $badgeCls = match($msg['estado']) {
                                    'Revisado'   => 'badge-revisado',
                                    'Contactado' => 'badge-contactado',
                                    'Cerrado'    => 'badge-cerrado',
                                    default      => 'badge-nuevo'
                                };
                            ?>
                            <div class="d-flex align-items-center gap-2 p-3 border-bottom border-dark-subtle" style="border-color:var(--admin-border)!important">
                                <div class="table-avatar" style="width:34px;height:34px;font-size:.78rem;flex-shrink:0">
                                    <?= strtoupper(substr($msg['nombre'], 0, 1)) ?>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <p class="mb-0 text-truncate fw-semibold text-white" style="font-size:.82rem">
                                        <?= htmlspecialchars($msg['nombre']) ?>
                                    </p>
                                    <p class="mb-0 text-truncate text-muted" style="font-size:.75rem">
                                        <?= htmlspecialchars(mb_strimwidth($msg['mensaje'], 0, 38, '…')) ?>
                                    </p>
                                </div>
                                <span class="badge-admin <?= $badgeCls ?>" style="flex-shrink:0"><?= $msg['estado'] ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 4: GRÁFICOS ANALÍTICOS (Barras & Sectores) -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-bar-chart-line me-2"></i>Proyectos por Cliente</h2>
                        </div>
                        <div class="admin-card-body">
                            <div class="chart-container" style="height:260px">
                                <canvas id="chartProyectos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h2 class="admin-card-title"><i class="bi bi-pie-chart-fill me-2"></i>Distribución de Clientes por Sector</h2>
                        </div>
                        <div class="admin-card-body">
                            <div class="chart-container" style="height:260px; display:flex; justify-content:center;">
                                <canvas id="chartSectores"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA 5: ACCESOS RÁPIDOS COMPLETOS Y RESPONSIVOS -->
            <div class="row g-3">
                <div class="col-12">
                    <h3 style="font-size:.82rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:.5rem">
                        Accesos Rápidos Directos
                    </h3>
                </div>
                <?php
                $accesos = [
                    ['/Proyecto_Servicios/admin/clientes/crear.php',     'bi-building-add',   'Nuevo Cliente',     'sc-purple'],
                    ['/Proyecto_Servicios/admin/proyectos/crear.php',    'bi-kanban',         'Nuevo Proyecto',    'sc-blue'],
                    ['/Proyecto_Servicios/admin/cotizaciones/listar.php','bi-receipt',        'Cotizaciones',      'sc-green'],
                    ['/Proyecto_Servicios/admin/servicios/crear.php',    'bi-gear-wide',      'Nuevo Servicio',    'sc-yellow'],
                    ['/Proyecto_Servicios/admin/contactos/listar.php',   'bi-envelope',       'Bandeja Mensajes',  'sc-red'],
                    ['/Proyecto_Servicios/admin/usuarios/listar.php',    'bi-people-fill',    'Usuarios Admin',    'sc-purple'],
                ];
                foreach ($accesos as $a): ?>
                <div class="col-6 col-sm-4 col-md-4 col-lg-2">
                    <a href="<?= $a[0] ?>" class="stat-card <?= $a[3] ?> d-flex flex-column align-items-center justify-content-center text-center text-decoration-none" style="padding:1.1rem;gap:.5rem">
                        <div class="stat-card-icon m-0" style="font-size:1.4rem"><i class="bi <?= $a[1] ?>"></i></div>
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
                'rgba(255,176,32,.7)', 'rgba(56,189,248,.7)',  'rgba(108,99,255,.5)'
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

// Chart.js — Clientes por Sector (Business Intelligence)
const ctxPie = document.getElementById('chartSectores').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($sectorLabels) ?>,
        datasets: [{
            data: <?= json_encode($sectorValues) ?>,
            backgroundColor: [
                '#6C63FF', '#FF6B6B', '#00D4AA', '#FFB020', '#38BDF8', '#9B51E0'
            ],
            borderWidth: 2,
            borderColor: '#1c2128'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { 
                position: 'right',
                labels: { color: '#A0A0B8', font: { family: 'Inter', size: 11 }, padding: 15 }
            },
            tooltip: {
                backgroundColor: '#1A1A2E',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
                titleColor: '#fff',
                bodyColor: '#A0A0B8'
            }
        }
    }
});
</script>
<script>
// Dashboard Realtime Polling & Sync (Senior Pro)
(function() {
    async function actualizarCotizacionesEnVivo() {
        try {
            const res = await fetch('/Proyecto_Servicios/admin/dashboard-cotizaciones-live.php', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            
            if (data.success) {
                // Actualizar números de KPIs
                const statTotal   = document.getElementById('statTotalCot');
                const statPend    = document.getElementById('statPendCot');
                const statVolumen = document.getElementById('statVolumenCot');
                const tbody       = document.getElementById('cotizacionesRealtimeBody');

                if (statTotal)   statTotal.textContent = data.stats.total;
                if (statPend)    statPend.textContent = data.stats.pendientes;
                if (statVolumen) statVolumen.textContent = 'S/ ' + data.stats.volumen;

                // Actualizar filas de la tabla si hay cotizaciones
                if (tbody && data.cotizaciones && data.cotizaciones.length > 0) {
                    let html = '';
                    data.cotizaciones.forEach(c => {
                        const badgeClass = c.estado === 'Pendiente' ? 'badge-nuevo' :
                                          (c.estado === 'Revisada' ? 'badge-revisado' :
                                          (c.estado === 'Contactado' ? 'badge-contactado' : 'badge-sus'));
                        html += `
                        <tr>
                            <td>
                                <span class="fw-semibold text-white">${c.nombre}</span>
                                ${c.empresa ? `<small class="text-muted d-block">${c.empresa}</small>` : ''}
                            </td>
                            <td class="fw-bold text-success">${c.presupuesto}</td>
                            <td><span class="badge-admin ${badgeClass}">${c.estado}</span></td>
                            <td>
                                <div style="color:var(--text-primary);font-weight:600">${c.fecha_dia}</div>
                                <small style="color:var(--text-muted);font-size:0.72rem"><i class="bi bi-clock me-1"></i>${c.fecha_hora}</small>
                            </td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                }
            }
        } catch (e) {
            // Silencioso para no interrumpir
        }
    }

    // Consultar cada 6 segundos en tiempo real
    setInterval(actualizarCotizacionesEnVivo, 6000);
})();
</script>
</body>
</html>
