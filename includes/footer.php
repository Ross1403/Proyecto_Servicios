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
// Lógica de Tema Oscuro/Claro (Dark Mode Toggle - Senior Pro)
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    if (themeToggle) {
        // Cargar preferencia
        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            
            if (body.classList.contains('light-mode')) {
                localStorage.setItem('theme', 'light');
            } else {
                localStorage.setItem('theme', 'dark');
            }
        });
    }
});
</script>
<style>
/* ==========================================================
   SENIOR PRO DARK/LIGHT MODE 
   Sobreescritura profunda de variables nativas de style.css
   ========================================================== */
body.light-mode {
    /* Fondos - Oscurecemos ligeramente el fondo para que las tarjetas blancas RESALTEN */
    --dark:          #F0F2F5; /* Gris-azulado sutil tipo UI moderno */
    --dark-2:        #FFFFFF; /* Footer blanco puro */
    --dark-3:        #E9ECEF;
    --dark-card:     #FFFFFF; /* Tarjetas blanco puro, ahora contrastarǭn contra #F0F2F5 */
    
    /* Textos */
    --text-primary:  #1F2937;
    --text-secondary:#4B5563;
    --text-muted:    #9CA3AF;
    
    /* Efectos Glassmorphism Invertidos */
    --glass:         rgba(255, 255, 255, 0.9);
    --glass-border:  rgba(0, 0, 0, 0.08);
    
    /* Sombras más suaves para modo claro */
    --shadow:        0 12px 35px rgba(108, 99, 255, 0.12); /* Sombra morada elegante en hover */
    --shadow-lg:     0 20px 60px rgba(0, 0, 0, 0.08);
}

/* ==========================================================
   SENIOR PRO FIX: Hacer que las tarjetas FLOTEN en modo claro
   ========================================================== */
body.light-mode .card-glass,
body.light-mode .project-card,
body.light-mode .client-card,
body.light-mode .caso-card,
body.light-mode .logro-card {
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.04); /* Sombra base permanente */
    border: 1px solid rgba(0, 0, 0, 0.06);
}
body.light-mode .card-glass:hover,
body.light-mode .project-card:hover,
body.light-mode .client-card:hover,
body.light-mode .caso-card:hover {
    box-shadow: var(--shadow);
    border-color: rgba(108, 99, 255, 0.3); /* Borde morado al pasar el mouse */
}

/* Ajustes específicos para que Navbar y Footer no se rompan */
body.light-mode #mainNav {
    background: rgba(255, 255, 255, 0.9) !important;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
body.light-mode #mainNav.scrolled {
    background: rgba(255, 255, 255, 0.98) !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
body.light-mode .navbar-brand,
body.light-mode .nav-link:hover,
body.light-mode .nav-link.active {
    color: #1F2937 !important;
}

/* ==========================================================
   ANIMATED TOGGLE SWITCH (UI SENIOR)
   ========================================================== */
.theme-switch-wrapper {
    display: flex;
    align-items: center;
}
.theme-switch {
    position: relative;
    width: 52px;
    height: 28px;
    background-color: #1A1A2E;
    border-radius: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 6px;
    transition: background-color 0.4s ease;
    border: 1px solid rgba(255,255,255,0.1);
}
.theme-switch i {
    font-size: 12px;
    z-index: 1;
}
.theme-switch .bi-moon-fill { color: #8B85FF; }
.theme-switch .bi-sun-fill { color: #FFB020; }

.theme-switch-indicator {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 22px;
    height: 22px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 0;
}
body.light-mode .theme-switch {
    background-color: #E9ECEF;
    border-color: rgba(0,0,0,0.1);
}
body.light-mode .theme-switch-indicator {
    transform: translateX(24px);
    background-color: #fff;
}
</style>
</body>
</html>
