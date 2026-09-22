<?php
// ============================================================
// index.php — Página de inicio de Devioz Proyectos
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Inicio';
$pageDesc  = 'Devioz Proyectos — Transformamos necesidades empresariales en soluciones tecnológicas. Consultora tecnológica líder en Perú.';

$db = getDB();

// ---- Contadores reales ----
$totalClientes  = $db->query("SELECT COUNT(*) FROM clientes WHERE estado = 1")->fetchColumn();
$totalProyectos = $db->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$totalLogros    = $db->query("SELECT COUNT(*) FROM logros")->fetchColumn();
$totalCasos     = $db->query("SELECT COUNT(*) FROM proyectos WHERE destacado = 1")->fetchColumn();

// ---- Servicios destacados (primeros 3 activos) ----
$stmtServ = $db->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY id_servicio LIMIT 6");
$servicios = $stmtServ->fetchAll();

// ---- Proyectos destacados ----
$stmtProy = $db->prepare("
    SELECT p.*, c.nombre AS cliente_nombre
    FROM proyectos p
    INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    WHERE p.destacado = 1
    ORDER BY p.id_proyecto DESC
    LIMIT 6
");
$stmtProy->execute();
$proyectos = $stmtProy->fetchAll();

// ---- Logros recientes destacados ----
$stmtLogros = $db->query("
    SELECT l.*, p.nombre AS proyecto_nombre, c.nombre AS cliente_nombre
    FROM logros l
    INNER JOIN proyectos p ON p.id_proyecto = l.id_proyecto
    INNER JOIN clientes c  ON c.id_cliente  = p.id_cliente
    ORDER BY l.porcentaje_mejora DESC
    LIMIT 4
");
$logros = $stmtLogros->fetchAll();

// ---- Clientes destacados ----
$stmtCli = $db->query("SELECT * FROM clientes WHERE destacado = 1 AND estado = 1 ORDER BY id_cliente LIMIT 8");
$clientes = $stmtCli->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- ===== HERO ===== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="hero-badge animate-on-scroll">
                    <i class="bi bi-stars"></i> Consultora Tecnológica Líder en Perú
                </div>
                <h1 class="hero-title animate-on-scroll delay-1">
                    Transformamos tus<br>
                    <span class="gradient-text">necesidades en</span><br>
                    soluciones reales
                </h1>
                <p class="hero-subtitle animate-on-scroll delay-2">
                    En Devioz Proyectos convertimos los desafíos empresariales en ventajas competitivas mediante tecnología de alto impacto, procesos ágiles y experiencia comprobada.
                </p>
                <div class="hero-cta animate-on-scroll delay-3">
                    <a href="proyectos.php" class="btn-primary-custom">
                        <i class="bi bi-kanban-fill"></i> Ver Proyectos
                    </a>
                    <a href="contacto.php" class="btn-outline-custom">
                        <i class="bi bi-chat-dots"></i> Hablar con un experto
                    </a>
                </div>
                <div class="hero-stats animate-on-scroll delay-4">
                    <div class="hero-stat-item">
                        <div class="hero-stat-value" data-counter="<?= $totalClientes ?>" data-suffix="+">0</div>
                        <div class="hero-stat-label">Clientes</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value" data-counter="<?= $totalProyectos ?>" data-suffix="+">0</div>
                        <div class="hero-stat-label">Proyectos</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value" data-counter="<?= $totalLogros ?>" data-suffix="+">0</div>
                        <div class="hero-stat-label">Logros medibles</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="hero-stat-value" data-counter="<?= $totalCasos ?>" data-suffix="+">0</div>
                        <div class="hero-stat-label">Casos de éxito</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-graphic position-relative">
                    <div class="hero-orb hero-orb-1"></div>
                    <div class="hero-orb hero-orb-2"></div>
                    <div class="hero-card-float">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="service-icon mb-0"><i class="bi bi-lightning-charge-fill"></i></div>
                            <div>
                                <p class="mb-0 fw-bold">Impacto real medible</p>
                                <p class="mb-0" style="font-size:.82rem;color:var(--text-secondary)">Resultados con indicadores concretos</p>
                            </div>
                        </div>
                        <?php foreach (array_slice($logros, 0, 3) as $l): ?>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span style="font-size:.8rem;color:var(--text-secondary);"><?= htmlspecialchars(mb_strimwidth($l['titulo'], 0, 35, '…')) ?></span>
                            <span class="fw-bold" style="color:var(--success);font-size:.85rem;">+<?= number_format($l['porcentaje_mejora'], 1) ?>%</span>
                        </div>
                        <div class="logro-progress">
                            <div class="logro-progress-bar" style="width:<?= min($l['porcentaje_mejora'], 100) ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATS ===== -->
<section class="stats-section py-0">
    <div class="container">
        <div class="row text-center g-0">
            <div class="col-6 col-md-3 border-end" style="border-color:var(--border)!important">
                <div class="stat-item">
                    <span class="stat-number" data-counter="<?= $totalClientes ?>">0</span>
                    <p class="stat-label">Clientes satisfechos</p>
                </div>
            </div>
            <div class="col-6 col-md-3 border-end" style="border-color:var(--border)!important">
                <div class="stat-item">
                    <span class="stat-number" data-counter="<?= $totalProyectos ?>">0</span>
                    <p class="stat-label">Proyectos entregados</p>
                </div>
            </div>
            <div class="col-6 col-md-3 border-end" style="border-color:var(--border)!important">
                <div class="stat-item">
                    <span class="stat-number" data-counter="<?= $totalLogros ?>">0</span>
                    <p class="stat-label">Logros medibles</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number" data-counter="98" data-suffix="%">0</span>
                    <p class="stat-label">Satisfacción de clientes</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== SERVICIOS ===== -->
<section id="servicios" class="bg-dark-2">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-gear-fill"></i> Servicios</span>
            <h2 class="section-title">Lo que hacemos mejor</h2>
            <p class="section-subtitle">Soluciones tecnológicas integrales diseñadas para generar resultados concretos en tu organización.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($servicios as $s): ?>
            <div class="col-md-6 col-lg-4 animate-on-scroll">
                <div class="card-glass h-100">
                    <div class="service-icon">
                        <i class="bi <?= htmlspecialchars($s['icono']) ?>"></i>
                    </div>
                    <h3 style="font-size:1.05rem;font-weight:700;margin-bottom:.6rem"><?= htmlspecialchars($s['nombre']) ?></h3>
                    <p style="font-size:.88rem;color:var(--text-secondary);line-height:1.65"><?= htmlspecialchars($s['descripcion']) ?></p>
                    <a href="servicios.php" class="btn-outline-custom mt-3" style="font-size:.82rem;padding:.5rem 1rem">
                        Conoce más <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="servicios.php" class="btn-primary-custom">
                <i class="bi bi-arrow-right-circle"></i> Ver todos los servicios
            </a>
        </div>
    </div>
</section>

<!-- ===== PROYECTOS DESTACADOS ===== -->
<section id="proyectos">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-kanban-fill"></i> Portafolio</span>
            <h2 class="section-title">Proyectos destacados</h2>
            <p class="section-subtitle">Casos reales que demuestran nuestra capacidad de transformar organizaciones con tecnología.</p>
        </div>
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
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge-estado <?= $estadoClass ?>"><?= htmlspecialchars($p['estado']) ?></span>
                            <small style="color:var(--text-muted);font-size:.75rem"><?= htmlspecialchars($p['cliente_nombre']) ?></small>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($p['nombre']) ?></h3>
                        <p style="font-size:.84rem;color:var(--text-secondary);line-height:1.6">
                            <?= htmlspecialchars(mb_strimwidth($p['descripcion'], 0, 120, '…')) ?>
                        </p>
                        <?php if ($p['tecnologias']): ?>
                        <div class="mt-2 mb-3" data-tech-tags="<?= htmlspecialchars($p['tecnologias']) ?>"></div>
                        <?php endif; ?>
                        <a href="proyecto.php?id=<?= $p['id_proyecto'] ?>" class="btn-outline-custom" style="font-size:.82rem;padding:.5rem 1rem">
                            Ver detalles <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="proyectos.php" class="btn-outline-custom">
                <i class="bi bi-grid"></i> Ver todos los proyectos
            </a>
        </div>
    </div>
</section>

<!-- ===== LOGROS KPI ===== -->
<section class="bg-dark-2">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-trophy-fill"></i> Resultados</span>
            <h2 class="section-title">Logros que hablan por nosotros</h2>
            <p class="section-subtitle">Medimos el éxito con números reales, no con promesas.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($logros as $l): ?>
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="logro-card">
                    <p style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.3rem">
                        <?= htmlspecialchars($l['cliente_nombre']) ?>
                    </p>
                    <h4 style="font-size:.95rem;font-weight:700;margin-bottom:.5rem"><?= htmlspecialchars($l['titulo']) ?></h4>
                    <div class="logro-pct <?= $l['porcentaje_mejora'] < 0 ? 'logro-pct-negative' : '' ?>">
                        <?= ($l['porcentaje_mejora'] >= 0 ? '+' : '') . number_format($l['porcentaje_mejora'], 1) ?>%
                    </div>
                    <div class="logro-progress">
                        <div class="logro-progress-bar" style="width:<?= min(abs($l['porcentaje_mejora']), 100) ?>%"></div>
                    </div>
                    <p style="font-size:.8rem;color:var(--text-muted)">
                        <?= number_format($l['indicador_anterior'], 0) ?> → <?= number_format($l['indicador_actual'], 0) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="casos-exito.php" class="btn-primary-custom">
                <i class="bi bi-stars"></i> Ver casos de éxito completos
            </a>
        </div>
    </div>
</section>

<!-- ===== CLIENTES ===== -->
<section id="clientes">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-building"></i> Clientes</span>
            <h2 class="section-title">Empresas que confían en Devioz</h2>
            <p class="section-subtitle">Trabajamos con líderes de industria en Perú y Latinoamérica.</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php foreach ($clientes as $c): ?>
            <div class="col-6 col-md-4 col-lg-3 animate-on-scroll">
                <a href="cliente.php?id=<?= $c['id_cliente'] ?>" class="d-block text-decoration-none">
                    <div class="client-card">
                        <?php if ($c['logo'] && file_exists('uploads/clientes/' . $c['logo'])): ?>
                            <img src="uploads/clientes/<?= htmlspecialchars($c['logo']) ?>"
                                 alt="<?= htmlspecialchars($c['nombre']) ?>"
                                 style="max-height:60px;max-width:100%;object-fit:contain;margin-bottom:1rem">
                        <?php else: ?>
                            <div class="client-logo"><?= strtoupper(mb_substr($c['nombre'], 0, 2)) ?></div>
                        <?php endif; ?>
                        <p class="fw-700 mb-0" style="font-size:.9rem;font-weight:700;color:var(--text-primary)"><?= htmlspecialchars($c['nombre']) ?></p>
                        <p style="font-size:.78rem;color:var(--text-muted)"><?= htmlspecialchars($c['sector']) ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="clientes.php" class="btn-outline-custom">
                <i class="bi bi-building"></i> Ver todos los clientes
            </a>
        </div>
    </div>
</section>

<!-- ===== CTA / CONTACTO ===== -->
<section class="bg-dark-2">
    <div class="container">
        <div class="card-glass text-center py-5 px-4" style="background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(255,107,107,.08))">
            <span class="section-badge mb-3"><i class="bi bi-chat-dots-fill"></i> ¿Tienes un proyecto?</span>
            <h2 class="section-title mb-3">Hablemos de tu próximo desafío</h2>
            <p class="section-subtitle mb-4">Cuéntanos qué necesitas y te conectamos con el equipo adecuado para hacerlo realidad.</p>
            <a href="contacto.php" class="btn-primary-custom">
                <i class="bi bi-send-fill"></i> Enviar mensaje
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
