<?php
// ============================================================
// proyectos.php — Listado de proyectos con filtro por estado
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Proyectos';
$pageDesc  = 'Portafolio de proyectos tecnológicos de Devioz Proyectos: software a medida, BI, integraciones y más.';

$db = getDB();

$estadoFiltro = trim($_GET['estado'] ?? '');
$estados = ['Planificado', 'En desarrollo', 'Finalizado', 'Suspendido'];

// Validar que el estado sea válido
if ($estadoFiltro && !in_array($estadoFiltro, $estados, true)) {
    $estadoFiltro = '';
}

$sql    = "SELECT p.*, c.nombre AS cliente_nombre FROM proyectos p INNER JOIN clientes c ON c.id_cliente = p.id_cliente";
$params = [];

if ($estadoFiltro) {
    $sql .= " WHERE p.estado = :estado";
    $params[':estado'] = $estadoFiltro;
}

$sql .= " ORDER BY p.destacado DESC, p.fecha_inicio DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$proyectos = $stmt->fetchAll();

// Conteo por estado
$conteos = $db->query("SELECT estado, COUNT(*) AS total FROM proyectos GROUP BY estado")->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="detail-hero">
    <div class="container text-center">
        <span class="section-badge"><i class="bi bi-kanban-fill"></i> Portafolio</span>
        <h1 class="section-title mt-2">Nuestro portafolio de proyectos</h1>
        <p class="section-subtitle mx-auto mt-3">Proyectos reales que han transformado organizaciones en múltiples industrias.</p>
    </div>
</section>

<section class="bg-dark-2">
    <div class="container">

        <!-- Filtros por estado -->
        <div class="d-flex gap-2 flex-wrap mb-4 align-items-center">
            <a href="proyectos.php"
               class="<?= !$estadoFiltro ? 'btn-primary-custom' : 'btn-outline-custom' ?>"
               style="font-size:.82rem;padding:.5rem 1rem"
               data-filter-estado="">
                Todos
                <span style="opacity:.6;font-size:.75rem">(<?= array_sum($conteos) ?>)</span>
            </a>
            <?php
            $badgeMap = [
                'Planificado'    => 'badge-planificado',
                'En desarrollo'  => 'badge-en-desarrollo',
                'Finalizado'     => 'badge-finalizado',
                'Suspendido'     => 'badge-suspendido',
            ];
            foreach ($estados as $e): ?>
            <a href="proyectos.php?estado=<?= urlencode($e) ?>"
               class="<?= ($estadoFiltro === $e) ? 'btn-primary-custom' : 'btn-outline-custom' ?>"
               style="font-size:.82rem;padding:.5rem 1rem">
                <?= htmlspecialchars($e) ?>
                <span style="opacity:.6;font-size:.75rem">(<?= $conteos[$e] ?? 0 ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($estadoFiltro): ?>
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem">
            Mostrando <?= count($proyectos) ?> proyecto(s) con estado "<?= htmlspecialchars($estadoFiltro) ?>"
        </p>
        <?php endif; ?>

        <!-- Grid de proyectos -->
        <?php if (empty($proyectos)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
            <i class="bi bi-inbox" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3">No hay proyectos con ese filtro.</p>
            <a href="proyectos.php" class="btn-outline-custom mt-2" style="font-size:.85rem;padding:.5rem 1.2rem">Ver todos</a>
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
            <div class="col-md-6 col-lg-4 animate-on-scroll">
                <div class="project-card">
                    <div class="project-card-img">
                        <?php if ($p['imagen'] && file_exists('uploads/proyectos/' . $p['imagen'])): ?>
                            <img src="uploads/proyectos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <?php else: ?>
                            <i class="bi bi-kanban"></i>
                        <?php endif; ?>
                    </div>
                    <div class="project-card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                            <span class="badge-estado <?= $estadoClass ?>"><?= htmlspecialchars($p['estado']) ?></span>
                            <?php if ($p['destacado']): ?>
                            <span class="badge-estado badge-finalizado"><i class="bi bi-star-fill"></i></span>
                            <?php endif; ?>
                        </div>
                        <p style="font-size:.72rem;color:var(--text-muted);margin-bottom:.3rem">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($p['cliente_nombre']) ?>
                        </p>
                        <h2 style="font-size:1rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($p['nombre']) ?></h2>
                        <p style="font-size:.84rem;color:var(--text-secondary);line-height:1.6">
                            <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 120, '…')) ?>
                        </p>
                        <?php if ($p['tecnologias']): ?>
                        <div class="mt-2 mb-3" data-tech-tags="<?= htmlspecialchars($p['tecnologias']) ?>"></div>
                        <?php endif; ?>
                        <?php if ($p['fecha_inicio']): ?>
                        <p style="font-size:.75rem;color:var(--text-muted)">
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

<?php include 'includes/footer.php'; ?>
