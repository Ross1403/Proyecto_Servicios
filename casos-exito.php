<?php
// ============================================================
// casos-exito.php — Proyectos marcados como destacados
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Casos de Éxito';
$pageDesc  = 'Casos de éxito reales de Devioz Proyectos: proyectos que generaron impacto medible en empresas líderes del Perú.';

$db = getDB();

$stmt = $db->query("
    SELECT p.*, c.nombre AS cliente_nombre, c.sector,
           (SELECT COUNT(*) FROM logros l WHERE l.id_proyecto = p.id_proyecto) AS total_logros,
           (SELECT AVG(l.porcentaje_mejora) FROM logros l WHERE l.id_proyecto = p.id_proyecto) AS avg_mejora
    FROM proyectos p
    INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    WHERE p.destacado = 1
    ORDER BY p.fecha_fin DESC, p.fecha_inicio DESC
");
$casos = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="detail-hero">
    <div class="container text-center">
        <span class="section-badge"><i class="bi bi-stars"></i> Casos de Éxito</span>
        <h1 class="section-title mt-2">Historias de impacto real</h1>
        <p class="section-subtitle mx-auto mt-3">
            Proyectos que transformaron operaciones, redujeron costos y aceleraron el crecimiento de empresas líderes.
        </p>
    </div>
</section>

<section class="bg-dark-2">
    <div class="container">
        <?php if (empty($casos)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
            <i class="bi bi-stars" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3">Aún no hay casos de éxito registrados.</p>
        </div>
        <?php else: ?>
        <div class="row g-5">
            <?php foreach ($casos as $i => $c): ?>
            <div class="col-lg-12 animate-on-scroll">
                <div class="caso-card">
                    <div class="caso-header">
                        <div class="row align-items-center gy-3">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                    <span class="badge-estado badge-finalizado"><i class="bi bi-star-fill me-1"></i>Caso de éxito</span>
                                    <span class="sector-badge" style="font-size:.72rem"><?= htmlspecialchars($c['sector']) ?></span>
                                    <span style="font-size:.75rem;color:var(--text-muted)">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($c['cliente_nombre']) ?>
                                    </span>
                                </div>
                                <h2 style="font-size:1.2rem;font-weight:800;color:var(--text-primary);margin-bottom:.5rem">
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </h2>
                                <p style="font-size:.88rem;color:var(--text-secondary);line-height:1.65;margin:0;max-width:620px">
                                    <?= htmlspecialchars(mb_strimwidth($c['descripcion'], 0, 200, '…')) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <?php if ($c['total_logros'] > 0): ?>
                                <div style="font-size:2.5rem;font-weight:900;color:var(--success);line-height:1">
                                    <?= number_format($c['avg_mejora'], 0) ?>%
                                </div>
                                <div style="font-size:.78rem;color:var(--text-muted)">Mejora promedio</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Obtener logros del caso
                    $stmtL = $db->prepare("SELECT * FROM logros WHERE id_proyecto = :id ORDER BY porcentaje_mejora DESC LIMIT 4");
                    $stmtL->execute([':id' => $c['id_proyecto']]);
                    $logrosCaso = $stmtL->fetchAll();
                    ?>

                    <?php if (!empty($logrosCaso)): ?>
                    <div class="p-4">
                        <p style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:1rem">
                            Indicadores de impacto
                        </p>
                        <div class="caso-kpis">
                            <?php foreach ($logrosCaso as $l): ?>
                            <div class="caso-kpi">
                                <div class="caso-kpi-value">
                                    <?= ($l['porcentaje_mejora'] >= 0 ? '+' : '') . number_format($l['porcentaje_mejora'], 0) ?>%
                                </div>
                                <div class="caso-kpi-label"><?= htmlspecialchars(mb_strimwidth($l['titulo'], 0, 40, '…')) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="p-4 pt-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div style="font-size:.78rem;color:var(--text-muted)">
                            <?php if ($c['tecnologias']): ?>
                            <span>Stack: </span>
                            <span data-tech-tags="<?= htmlspecialchars($c['tecnologias']) ?>"></span>
                            <?php endif; ?>
                        </div>
                        <a href="proyecto.php?id=<?= $c['id_proyecto'] ?>" class="btn-primary-custom" style="font-size:.85rem;padding:.55rem 1.2rem">
                            Ver caso completo <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section>
    <div class="container text-center">
        <h2 class="section-title mb-3">¿Tu empresa podría ser el próximo caso de éxito?</h2>
        <p class="section-subtitle mb-4">Cuéntanos tu desafío y construyamos juntos los resultados que tu negocio merece.</p>
        <a href="contacto.php" class="btn-primary-custom"><i class="bi bi-send-fill"></i> Hablemos</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
