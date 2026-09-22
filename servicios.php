<?php
// ============================================================
// servicios.php — Listado de servicios desde BD
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Servicios';
$pageDesc  = 'Conoce todos los servicios tecnológicos de Devioz Proyectos: desarrollo de software, BI, transformación digital y más.';

$db = getDB();
$stmt = $db->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY id_servicio");
$servicios = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="detail-hero">
    <div class="container text-center">
        <span class="section-badge"><i class="bi bi-gear-fill"></i> Servicios</span>
        <h1 class="section-title mt-2">Nuestros servicios tecnológicos</h1>
        <p class="section-subtitle mx-auto mt-3">Soluciones especializadas que se adaptan a las necesidades específicas de tu empresa.</p>
    </div>
</section>

<section class="bg-dark-2">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($servicios as $i => $s): ?>
            <div class="col-md-6 col-lg-4 animate-on-scroll" style="animation-delay:<?= $i * 0.08 ?>s">
                <div class="card-glass h-100">
                    <div class="service-icon">
                        <i class="bi <?= htmlspecialchars($s['icono']) ?>"></i>
                    </div>
                    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem"><?= htmlspecialchars($s['nombre']) ?></h2>
                    <p style="font-size:.9rem;color:var(--text-secondary);line-height:1.7;flex:1"><?= htmlspecialchars($s['descripcion']) ?></p>
                    <a href="contacto.php?servicio=<?= urlencode($s['nombre']) ?>" class="btn-outline-custom mt-3" style="font-size:.82rem;padding:.5rem 1rem">
                        <i class="bi bi-chat-dots"></i> Solicitar información
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Proceso de trabajo -->
<section>
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-diagram-3"></i> Metodología</span>
            <h2 class="section-title mt-2">Cómo trabajamos</h2>
        </div>
        <div class="row g-4">
            <?php
            $pasos = [
                ['01', 'bi-search', 'Diagnóstico', 'Analizamos en profundidad tu situación actual, procesos, pain points y objetivos de negocio.'],
                ['02', 'bi-lightbulb', 'Propuesta', 'Diseñamos una solución a medida con alcance, tecnología, plazos y KPIs claramente definidos.'],
                ['03', 'bi-code-slash', 'Desarrollo', 'Implementamos con metodologías ágiles, con entregas iterativas y revisiones constantes.'],
                ['04', 'bi-graph-up', 'Medición', 'Medimos los resultados con los indicadores acordados y ajustamos para maximizar el impacto.'],
            ];
            foreach ($pasos as $paso): ?>
            <div class="col-md-6 col-lg-3 animate-on-scroll">
                <div class="card-glass text-center h-100">
                    <div style="font-size:2rem;font-weight:900;color:var(--primary);opacity:.3;line-height:1"><?= $paso[0] ?></div>
                    <div class="service-icon mx-auto mt-1"><i class="bi <?= $paso[1] ?>"></i></div>
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem"><?= $paso[2] ?></h3>
                    <p style="font-size:.85rem;color:var(--text-secondary);line-height:1.65"><?= $paso[3] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-dark-2">
    <div class="container text-center">
        <h2 class="section-title mb-3">¿Cuál de estos servicios necesitas?</h2>
        <p class="section-subtitle mb-4">Cuéntanos tu desafío y te proponemos la solución más adecuada.</p>
        <a href="contacto.php" class="btn-primary-custom"><i class="bi bi-send-fill"></i> Hablar con un experto</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
