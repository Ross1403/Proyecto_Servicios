<?php
// ============================================================
// cliente.php — Detalle de cliente con proyectos y logros (JOIN real)
// ============================================================
require_once 'config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: clientes.php');
    exit;
}

$db = getDB();

// ---- Datos del cliente ----
$stmtC = $db->prepare("SELECT * FROM clientes WHERE id_cliente = :id AND estado = 1");
$stmtC->execute([':id' => $id]);
$cliente = $stmtC->fetch();

if (!$cliente) {
    header('Location: clientes.php');
    exit;
}

// ---- Proyectos del cliente con conteo de logros ----
$stmtP = $db->prepare("
    SELECT p.*,
           (SELECT COUNT(*) FROM logros l WHERE l.id_proyecto = p.id_proyecto) AS total_logros
    FROM proyectos p
    WHERE p.id_cliente = :id
    ORDER BY p.fecha_inicio ASC
");
$stmtP->execute([':id' => $id]);
$proyectos = $stmtP->fetchAll();

// ---- Logros de los proyectos de este cliente ----
$stmtL = $db->prepare("
    SELECT l.*, p.nombre AS proyecto_nombre
    FROM logros l
    INNER JOIN proyectos p ON p.id_proyecto = l.id_proyecto
    WHERE p.id_cliente = :id
    ORDER BY l.porcentaje_mejora DESC
");
$stmtL->execute([':id' => $id]);
$logros = $stmtL->fetchAll();

$pageTitle = htmlspecialchars($cliente['nombre']);
$pageDesc  = 'Proyectos y logros de ' . $cliente['nombre'] . ' con Devioz Proyectos.';

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero -->
<section class="detail-hero">
    <div class="container">
        <nav aria-label="breadcrumb" style="margin-bottom:1.5rem">
            <ol style="display:flex;gap:.5rem;font-size:.8rem;color:var(--text-muted);list-style:none;padding:0">
                <li><a href="index.php" style="color:var(--primary-light);text-decoration:none">Inicio</a> /</li>
                <li><a href="clientes.php" style="color:var(--primary-light);text-decoration:none">Clientes</a> /</li>
                <li><?= htmlspecialchars($cliente['nombre']) ?></li>
            </ol>
        </nav>
        <div class="row align-items-center gy-4">
            <div class="col-lg-8">
                <span class="sector-badge"><i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($cliente['sector']) ?></span>
                <h1 class="section-title"><?= htmlspecialchars($cliente['nombre']) ?></h1>
                <p style="color:var(--text-secondary);line-height:1.8;margin-top:1rem;max-width:700px">
                    <?= htmlspecialchars($cliente['descripcion']) ?>
                </p>
                <?php if ($cliente['web']): ?>
                <a href="<?= htmlspecialchars($cliente['web']) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn-outline-custom mt-3" style="font-size:.85rem;padding:.5rem 1.1rem">
                    <i class="bi bi-globe me-1"></i> Visitar sitio web
                </a>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php if ($cliente['logo'] && file_exists('uploads/clientes/' . $cliente['logo'])): ?>
                    <img src="uploads/clientes/<?= htmlspecialchars($cliente['logo']) ?>"
                         alt="Logo <?= htmlspecialchars($cliente['nombre']) ?>"
                         style="max-height:100px;max-width:200px;object-fit:contain">
                <?php else: ?>
                    <div class="client-logo" style="width:100px;height:100px;font-size:2.5rem;margin-left:auto">
                        <?= strtoupper(mb_substr($cliente['nombre'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Métricas del cliente -->
        <div class="row g-3 mt-4">
            <div class="col-6 col-md-3">
                <div class="card-glass text-center py-3">
                    <div style="font-size:2rem;font-weight:900;color:var(--primary-light)"><?= count($proyectos) ?></div>
                    <div style="font-size:.8rem;color:var(--text-muted)">Proyectos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-glass text-center py-3">
                    <div style="font-size:2rem;font-weight:900;color:var(--success)"><?= count($logros) ?></div>
                    <div style="font-size:.8rem;color:var(--text-muted)">Logros medibles</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-glass text-center py-3">
                    <div style="font-size:2rem;font-weight:900;color:var(--accent)">
                        <?= count(array_filter($proyectos, fn($p) => $p['estado'] === 'Finalizado')) ?>
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted)">Proyectos finalizados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-glass text-center py-3">
                    <div style="font-size:2rem;font-weight:900;color:var(--warning)">
                        <?php
                        $maxPct = $logros ? max(array_column($logros, 'porcentaje_mejora')) : 0;
                        echo number_format($maxPct, 0) . '%';
                        ?>
                    </div>
                    <div style="font-size:.8rem;color:var(--text-muted)">Mejor mejora lograda</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Proyectos -->
<section class="bg-dark-2">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge"><i class="bi bi-kanban-fill"></i> Proyectos</span>
            <h2 class="section-title mt-2">Proyectos realizados</h2>
        </div>
        <?php if (empty($proyectos)): ?>
        <div class="text-center py-4" style="color:var(--text-muted)">
            <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3"></i>
            <p class="mt-2">Aún no hay proyectos registrados para este cliente.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($proyectos as $p):
                $estadoClass = match($p['estado']) {
                    'Finalizado'    => 'badge-finalizado',
                    'En desarrollo' => 'badge-en-desarrollo',
                    'Planificado'   => 'badge-planificado',
                    'Suspendido'    => 'badge-suspendido',
                    default         => 'badge-planificado'
                };
            ?>
            <div class="col-md-6 animate-on-scroll">
                <div class="project-card">
                    <div class="project-card-img" style="height:140px">
                        <?php if ($p['imagen'] && file_exists('uploads/proyectos/' . $p['imagen'])): ?>
                            <img src="uploads/proyectos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <?php else: ?>
                            <i class="bi bi-kanban"></i>
                        <?php endif; ?>
                    </div>
                    <div class="project-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge-estado <?= $estadoClass ?>"><?= htmlspecialchars($p['estado']) ?></span>
                            <?php if ($p['destacado']): ?>
                            <span class="badge-estado badge-finalizado"><i class="bi bi-star-fill me-1"></i>Caso de éxito</span>
                            <?php endif; ?>
                            <?php if ($p['total_logros'] > 0): ?>
                            <span style="font-size:.72rem;color:var(--success)"><i class="bi bi-trophy-fill me-1"></i><?= $p['total_logros'] ?> logro(s)</span>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($p['nombre']) ?></h3>
                        <p style="font-size:.84rem;color:var(--text-secondary);line-height:1.6">
                            <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 140, '…')) ?>
                        </p>
                        <?php if ($p['tecnologias']): ?>
                        <div class="my-2" data-tech-tags="<?= htmlspecialchars($p['tecnologias']) ?>"></div>
                        <?php endif; ?>
                        <?php if ($p['fecha_inicio']): ?>
                        <p style="font-size:.75rem;color:var(--text-muted);margin-top:.5rem">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= date('M Y', strtotime($p['fecha_inicio'])) ?>
                            <?= $p['fecha_fin'] ? ' — ' . date('M Y', strtotime($p['fecha_fin'])) : ' — Presente' ?>
                        </p>
                        <?php endif; ?>
                        <a href="proyecto.php?id=<?= $p['id_proyecto'] ?>" class="btn-outline-custom mt-2" style="font-size:.82rem;padding:.45rem .9rem">
                            Ver detalles <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Logros -->
<?php if (!empty($logros)): ?>
<section>
    <div class="container">
        <div class="mb-4">
            <span class="section-badge"><i class="bi bi-trophy-fill"></i> Resultados</span>
            <h2 class="section-title mt-2">Logros e impacto generado</h2>
        </div>
        <div class="row g-3">
            <?php foreach ($logros as $l): ?>
            <div class="col-md-6 col-lg-4 animate-on-scroll">
                <div class="logro-card">
                    <p style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem">
                        <?= htmlspecialchars($l['proyecto_nombre']) ?>
                    </p>
                    <h4 style="font-size:.95rem;font-weight:700;margin-bottom:.4rem"><?= htmlspecialchars($l['titulo']) ?></h4>
                    <p style="font-size:.83rem;color:var(--text-secondary);line-height:1.5;margin-bottom:.75rem">
                        <?= htmlspecialchars($l['descripcion']) ?>
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="logro-pct"><?= ($l['porcentaje_mejora'] >= 0 ? '+' : '') . number_format($l['porcentaje_mejora'], 1) ?>%</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">
                            <?= number_format($l['indicador_anterior'], 0) ?> → <?= number_format($l['indicador_actual'], 0) ?>
                        </div>
                    </div>
                    <div class="logro-progress">
                        <div class="logro-progress-bar" style="width:<?= min(abs($l['porcentaje_mejora']), 100) ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
