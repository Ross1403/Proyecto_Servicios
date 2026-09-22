<?php
// ============================================================
// proyecto.php — Detalle de proyecto con logros asociados
// ============================================================
require_once 'config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: proyectos.php');
    exit;
}

$db = getDB();

// ---- Proyecto con datos del cliente ----
$stmtP = $db->prepare("
    SELECT p.*, c.nombre AS cliente_nombre, c.id_cliente, c.sector
    FROM proyectos p
    INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    WHERE p.id_proyecto = :id
");
$stmtP->execute([':id' => $id]);
$proyecto = $stmtP->fetch();

if (!$proyecto) {
    header('Location: proyectos.php');
    exit;
}

// ---- Logros del proyecto ----
$stmtL = $db->prepare("SELECT * FROM logros WHERE id_proyecto = :id ORDER BY porcentaje_mejora DESC");
$stmtL->execute([':id' => $id]);
$logros = $stmtL->fetchAll();

// ---- Otros proyectos del mismo cliente ----
$stmtRel = $db->prepare("
    SELECT id_proyecto, nombre, estado FROM proyectos
    WHERE id_cliente = :cid AND id_proyecto != :pid
    LIMIT 3
");
$stmtRel->execute([':cid' => $proyecto['id_cliente'], ':pid' => $id]);
$relacionados = $stmtRel->fetchAll();

$pageTitle = htmlspecialchars($proyecto['nombre']);
$pageDesc  = 'Proyecto ' . $proyecto['nombre'] . ' de ' . $proyecto['cliente_nombre'] . ' — Devioz Proyectos.';

$estadoClass = match($proyecto['estado']) {
    'Finalizado'    => 'badge-finalizado',
    'En desarrollo' => 'badge-en-desarrollo',
    'Planificado'   => 'badge-planificado',
    'Suspendido'    => 'badge-suspendido',
    default         => 'badge-planificado'
};

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Proyecto -->
<section class="detail-hero">
    <div class="container">
        <nav aria-label="breadcrumb" style="margin-bottom:1.5rem">
            <ol style="display:flex;gap:.5rem;font-size:.8rem;color:var(--text-muted);list-style:none;padding:0;flex-wrap:wrap">
                <li><a href="index.php" style="color:var(--primary-light);text-decoration:none">Inicio</a> /</li>
                <li><a href="proyectos.php" style="color:var(--primary-light);text-decoration:none">Proyectos</a> /</li>
                <li><?= htmlspecialchars($proyecto['nombre']) ?></li>
            </ol>
        </nav>

        <div class="row align-items-start gy-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                    <span class="badge-estado <?= $estadoClass ?>"><?= htmlspecialchars($proyecto['estado']) ?></span>
                    <?php if ($proyecto['destacado']): ?>
                    <span class="badge-estado badge-finalizado"><i class="bi bi-star-fill me-1"></i>Caso de éxito</span>
                    <?php endif; ?>
                    <span class="sector-badge"><i class="bi bi-building me-1"></i>
                        <a href="cliente.php?id=<?= $proyecto['id_cliente'] ?>" style="color:inherit;text-decoration:none">
                            <?= htmlspecialchars($proyecto['cliente_nombre']) ?>
                        </a>
                    </span>
                </div>

                <h1 class="section-title"><?= htmlspecialchars($proyecto['nombre']) ?></h1>
                <p style="color:var(--text-secondary);line-height:1.8;margin-top:1rem;max-width:720px">
                    <?= htmlspecialchars($proyecto['descripcion']) ?>
                </p>

                <?php if ($proyecto['tecnologias']): ?>
                <div class="mt-3 mb-1" style="font-size:.78rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Stack tecnológico</div>
                <div data-tech-tags="<?= htmlspecialchars($proyecto['tecnologias']) ?>"></div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card-glass">
                    <h5 style="font-size:.85rem;font-weight:700;margin-bottom:1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px">Detalles del proyecto</h5>
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.85rem;color:var(--text-muted)"><i class="bi bi-calendar3 me-2"></i>Inicio</span>
                            <span style="font-size:.85rem;font-weight:600">
                                <?= $proyecto['fecha_inicio'] ? date('d M Y', strtotime($proyecto['fecha_inicio'])) : 'N/D' ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.85rem;color:var(--text-muted)"><i class="bi bi-calendar-check me-2"></i>Fin</span>
                            <span style="font-size:.85rem;font-weight:600">
                                <?= $proyecto['fecha_fin'] ? date('d M Y', strtotime($proyecto['fecha_fin'])) : 'En curso' ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.85rem;color:var(--text-muted)"><i class="bi bi-trophy me-2"></i>Logros</span>
                            <span style="font-size:.85rem;font-weight:600;color:var(--success)"><?= count($logros) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.85rem;color:var(--text-muted)"><i class="bi bi-tag me-2"></i>Sector</span>
                            <span style="font-size:.85rem;font-weight:600"><?= htmlspecialchars($proyecto['sector']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($proyecto['imagen'] && file_exists('uploads/proyectos/' . $proyecto['imagen'])): ?>
        <div class="mt-4">
            <img src="uploads/proyectos/<?= htmlspecialchars($proyecto['imagen']) ?>"
                 alt="Imagen <?= htmlspecialchars($proyecto['nombre']) ?>"
                 style="width:100%;max-height:400px;object-fit:cover;border-radius:var(--radius-lg);border:1px solid var(--glass-border)">
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Logros -->
<?php if (!empty($logros)): ?>
<section class="bg-dark-2">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge"><i class="bi bi-trophy-fill"></i> Resultados</span>
            <h2 class="section-title mt-2">Logros e indicadores de éxito</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($logros as $l): ?>
            <div class="col-md-6 col-lg-4 animate-on-scroll">
                <div class="caso-card">
                    <div class="caso-header">
                        <h4 style="font-size:.95rem;font-weight:700;color:var(--text-primary);margin:0"><?= htmlspecialchars($l['titulo']) ?></h4>
                    </div>
                    <div class="p-3">
                        <p style="font-size:.85rem;color:var(--text-secondary);line-height:1.6;margin-bottom:1rem">
                            <?= htmlspecialchars($l['descripcion']) ?>
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <div class="caso-kpi flex-fill">
                                <div class="caso-kpi-value" style="color:var(--text-muted);font-size:1.2rem">
                                    <?= number_format($l['indicador_anterior'], 0) ?>
                                </div>
                                <div class="caso-kpi-label">Antes</div>
                            </div>
                            <div class="caso-kpi flex-fill">
                                <div class="caso-kpi-value">
                                    <?= number_format($l['indicador_actual'], 0) ?>
                                </div>
                                <div class="caso-kpi-label">Después</div>
                            </div>
                            <div class="caso-kpi flex-fill">
                                <div class="caso-kpi-value" style="color:var(--primary-light)">
                                    <?= ($l['porcentaje_mejora'] >= 0 ? '+' : '') . number_format($l['porcentaje_mejora'], 1) ?>%
                                </div>
                                <div class="caso-kpi-label">Mejora</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Proyectos relacionados -->
<?php if (!empty($relacionados)): ?>
<section>
    <div class="container">
        <div class="mb-4">
            <span class="section-badge"><i class="bi bi-grid"></i> Más proyectos</span>
            <h2 class="section-title mt-2">Otros proyectos de <?= htmlspecialchars($proyecto['cliente_nombre']) ?></h2>
        </div>
        <div class="row g-3">
            <?php foreach ($relacionados as $r):
                $eClass = match($r['estado']) {
                    'Finalizado'    => 'badge-finalizado',
                    'En desarrollo' => 'badge-en-desarrollo',
                    default         => 'badge-planificado'
                };
            ?>
            <div class="col-md-4">
                <div class="card-glass h-100">
                    <span class="badge-estado <?= $eClass ?> mb-2"><?= htmlspecialchars($r['estado']) ?></span>
                    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($r['nombre']) ?></h3>
                    <a href="proyecto.php?id=<?= $r['id_proyecto'] ?>" class="btn-outline-custom mt-2" style="font-size:.8rem;padding:.4rem .8rem">
                        Ver proyecto <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
