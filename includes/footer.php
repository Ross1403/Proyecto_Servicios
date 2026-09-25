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

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS público -->
<script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
