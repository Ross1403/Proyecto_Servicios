<?php
// ============================================================
// includes/footer.php — Pie de página público
// ============================================================
$depth = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
if ($depth <= 1) $base = '';
?>
<footer class="footer-main">
    <div class="container">
        <div class="row gy-4">
            <!-- Brand & desc -->
            <div class="col-lg-4">
                <a class="footer-brand" href="<?= $base ?>index.php">
                    <i class="bi bi-layers-fill me-2"></i>Devioz<span>.</span>
                </a>
                <p class="footer-tagline mt-3">
                    Transformamos necesidades empresariales en soluciones tecnológicas.
                </p>
                <div class="footer-social mt-3">
                    <a href="#" aria-label="LinkedIn de Devioz"><i class="bi bi-linkedin"></i></a>
                    <a href="#" aria-label="Twitter de Devioz"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Instagram de Devioz"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <!-- Quick links -->
            <div class="col-lg-2 col-md-4">
                <h6 class="footer-heading">Empresa</h6>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>nosotros.php">Nosotros</a></li>
                    <li><a href="<?= $base ?>servicios.php">Servicios</a></li>
                    <li><a href="<?= $base ?>clientes.php">Clientes</a></li>
                    <li><a href="<?= $base ?>proyectos.php">Proyectos</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading">Servicios</h6>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>servicios.php">Desarrollo de Software</a></li>
                    <li><a href="<?= $base ?>servicios.php">Transformación Digital</a></li>
                    <li><a href="<?= $base ?>servicios.php">Business Intelligence</a></li>
                    <li><a href="<?= $base ?>servicios.php">Consultoría TI</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading">Contacto</h6>
                <ul class="footer-links">
                    <li><i class="bi bi-envelope me-2"></i><a href="mailto:contacto@devioz.com">contacto@devioz.com</a></li>
                    <li><i class="bi bi-telephone me-2"></i><a href="tel:+51014567890">+51 (01) 456-7890</a></li>
                    <li><i class="bi bi-geo-alt me-2"></i>NRO. 0 DPTO. 302 URB. MANZANILLA (BLOCK G08) LIMA - LIMA - LIMA</li>
                    <li>
                        <a href="<?= $base ?>contacto.php" class="btn btn-accent btn-sm mt-2">
                            <i class="bi bi-chat-dots me-1"></i>Enviar mensaje
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center footer-bottom">
            <p class="mb-0">&copy; <?= date('Y') ?> Devioz Proyectos. Todos los derechos reservados.</p>
            <p class="mb-0 mt-2 mt-md-0">
                Hecho con <i class="bi bi-heart-fill text-accent"></i> en Lima, Perú
            </p>
        </div>
    </div>
</footer>

<?php
// Lógica para Botón de WhatsApp Inteligente (Senior Pro)
$currentUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$waMessage = urlencode("Hola Devioz, estoy interesado en sus servicios. Vi esto en su web: " . $currentUrl);
$waNumber = "51999888777"; // Número de la agencia
?>

<!-- Botón Flotante WhatsApp -->
<a href="https://wa.me/<?= $waNumber ?>?text=<?= $waMessage ?>" target="_blank" class="wa-float-btn" aria-label="Contactar por WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<style>
/* CSS para Botón WhatsApp */
.wa-float-btn {
    position: fixed;
    width: 60px;
    height: 60px;
    bottom: 30px;
    right: 30px;
    background-color: #25D366;
    color: white;
    border-radius: 50px;
    text-align: center;
    font-size: 30px;
    box-shadow: 0px 4px 15px rgba(37, 211, 102, 0.4);
    z-index: 1000;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}
.wa-float-btn:hover {
    transform: scale(1.1);
    color: white;
    box-shadow: 0px 6px 20px rgba(37, 211, 102, 0.6);
}
</style>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS público -->
<script src="<?= $base ?>assets/js/main.js"></script>
<script>
// Lógica de Tema Oscuro/Claro (Dark Mode Toggle)
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const body = document.body;

    if (themeToggle) {
        // Cargar preferencia
        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
            themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            
            if (body.classList.contains('light-mode')) {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
            } else {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
            }
        });
    }
});
</script>
<style>
/* Base ligera para el Dark Mode toggle si su CSS no tiene soporte nativo aún */
body.light-mode {
    --bg-color: #ffffff;
    --text-color: #212529;
    --card-bg: #f8f9fa;
    --glass-bg: rgba(255, 255, 255, 0.8);
    background-color: var(--bg-color);
    color: var(--text-color);
}
body.light-mode .navbar {
    background-color: #ffffff !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
body.light-mode .navbar .nav-link,
body.light-mode .navbar .navbar-brand {
    color: #212529 !important;
}
body.light-mode #themeToggle {
    color: #212529 !important;
}
</style>
</body>
</html>
