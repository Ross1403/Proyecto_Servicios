<?php
// ============================================================
// nosotros.php — Página "Sobre Nosotros"
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Nosotros';
$pageDesc  = 'Conoce al equipo y la misión de Devioz Proyectos, consultora tecnológica comprometida con la transformación digital de las empresas.';

$db = getDB();
$totalClientes  = $db->query("SELECT COUNT(*) FROM clientes WHERE estado = 1")->fetchColumn();
$totalProyectos = $db->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$totalLogros    = $db->query("SELECT COUNT(*) FROM logros")->fetchColumn();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Nosotros -->
<section class="detail-hero">
    <div class="container">
        <div class="text-center">
            <span class="section-badge"><i class="bi bi-people-fill"></i> Nosotros</span>
            <h1 class="section-title mt-2">Más que una consultora,<br><span style="color:var(--primary-light)">somos tu equipo tecnológico</span></h1>
            <p class="section-subtitle mx-auto mt-3">
                Fundados con el propósito de hacer que la tecnología sea un motor real de crecimiento para las empresas peruanas y latinoamericanas.
            </p>
        </div>
    </div>
</section>

<!-- Misión, Visión, Valores -->
<section class="bg-dark-2">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 animate-on-scroll">
                <div class="card-glass h-100">
                    <div class="service-icon"><i class="bi bi-bullseye"></i></div>
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem">Misión</h3>
                    <p style="font-size:.9rem;color:var(--text-secondary);line-height:1.7">
                        Transformar las necesidades empresariales en soluciones tecnológicas de alto impacto que generen ventajas competitivas reales y medibles para nuestros clientes.
                    </p>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll delay-1">
                <div class="card-glass h-100">
                    <div class="service-icon"><i class="bi bi-eye-fill"></i></div>
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem">Visión</h3>
                    <p style="font-size:.9rem;color:var(--text-secondary);line-height:1.7">
                        Ser la consultora tecnológica de referencia en Latinoamérica, reconocida por la calidad de sus soluciones, la medibilidad de sus resultados y el compromiso con el éxito de sus clientes.
                    </p>
                </div>
            </div>
            <div class="col-md-4 animate-on-scroll delay-2">
                <div class="card-glass h-100">
                    <div class="service-icon"><i class="bi bi-heart-fill"></i></div>
                    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem">Valores</h3>
                    <ul style="font-size:.9rem;color:var(--text-secondary);line-height:2;padding-left:1rem">
                        <li>Excelencia técnica</li>
                        <li>Transparencia y confianza</li>
                        <li>Resultados medibles</li>
                        <li>Innovación continua</li>
                        <li>Compromiso con el cliente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nuestra historia -->
<section>
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6 animate-on-scroll">
                <span class="section-badge"><i class="bi bi-clock-history"></i> Nuestra historia</span>
                <h2 class="section-title mt-2">Una empresa nacida de la experiencia</h2>
                <p style="color:var(--text-secondary);line-height:1.8;margin-top:1rem">
                    Devioz Proyectos nació de la convicción de que la tecnología debería ser accesible, efectiva y medible para empresas de todos los tamaños. Fundada por un equipo de consultores con experiencia en grandes corporaciones peruanas, decidimos crear una empresa donde la innovación tecnológica estuviera al servicio real del negocio.
                </p>
                <p style="color:var(--text-secondary);line-height:1.8;margin-top:1rem">
                    Desde nuestros primeros proyectos con empresas del sector financiero y telecomunicaciones, hemos crecido para servir a clientes de industrias tan diversas como alimentos, retail, gastronomía y consumo masivo.
                </p>
                <div class="d-flex gap-4 mt-4 flex-wrap">
                    <div>
                        <div style="font-size:2rem;font-weight:900;color:var(--primary-light)" data-counter="<?= $totalClientes ?>">0</div>
                        <div style="font-size:.82rem;color:var(--text-muted)">Clientes</div>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:900;color:var(--accent)" data-counter="<?= $totalProyectos ?>">0</div>
                        <div style="font-size:.82rem;color:var(--text-muted)">Proyectos</div>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:900;color:var(--success)" data-counter="<?= $totalLogros ?>">0</div>
                        <div style="font-size:.82rem;color:var(--text-muted)">Logros</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 animate-on-scroll delay-2">
                <div class="card-glass">
                    <h4 style="font-weight:700;margin-bottom:1.5rem">¿Por qué elegir Devioz?</h4>
                    <?php
                    $diferenciadores = [
                        ['bi-graph-up-arrow', 'Resultados medibles', 'No entregamos proyectos, entregamos impacto. Cada solución tiene KPIs definidos desde el inicio.'],
                        ['bi-people-fill', 'Equipo senior', 'Nuestro equipo está conformado por profesionales con experiencia en empresas líderes de Perú y LATAM.'],
                        ['bi-puzzle-fill', 'Soluciones a medida', 'No usamos plantillas. Cada proyecto se diseña desde cero para encajar perfectamente con tu realidad.'],
                        ['bi-headset', 'Soporte continuo', 'La relación no termina en la entrega. Somos tu aliado tecnológico en el largo plazo.'],
                    ];
                    foreach ($diferenciadores as $d): ?>
                    <div class="d-flex gap-3 mb-3">
                        <div class="service-icon mb-0" style="width:40px;height:40px;font-size:1.1rem;flex-shrink:0">
                            <i class="bi <?= $d[0] ?>"></i>
                        </div>
                        <div>
                            <p class="mb-1 fw-bold" style="font-size:.9rem"><?= $d[1] ?></p>
                            <p class="mb-0" style="font-size:.83rem;color:var(--text-secondary)"><?= $d[2] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-dark-2">
    <div class="container text-center">
        <h2 class="section-title mb-3">¿Listo para transformar tu empresa?</h2>
        <p class="section-subtitle mb-4">Conversemos sobre tu próximo proyecto tecnológico.</p>
        <a href="contacto.php" class="btn-primary-custom">
            <i class="bi bi-send-fill"></i> Contáctanos
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
