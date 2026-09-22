<?php
// ============================================================
// includes/navbar.php — Barra de navegación pública
// ============================================================

// Detectar página activa
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// Calcular ruta base
$depth = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
if ($depth <= 1) $base = '';

function navLink(string $href, string $label, string $current): string {
    $active = ($current === basename($href)) ? 'active' : '';
    return "<a class=\"nav-link {$active}\" href=\"{$href}\">{$label}</a>";
}
?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?= $base ?>index.php">
            <span class="brand-icon"><i class="bi bi-layers-fill"></i></span>
            Devioz<span class="brand-accent">.</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false"
                aria-label="Abrir menú de navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <?= navLink($base . 'index.php', 'Inicio', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'nosotros.php', 'Nosotros', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'servicios.php', 'Servicios', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'clientes.php', 'Clientes', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'proyectos.php', 'Proyectos', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'casos-exito.php', 'Casos de Éxito', $currentPage) ?>
                </li>
                <li class="nav-item">
                    <?= navLink($base . 'contacto.php', 'Contacto', $currentPage) ?>
                </li>
                <li class="nav-item ms-lg-2">
                    <a href="<?= $base ?>admin/login.php" class="btn btn-accent btn-sm px-3">
                        <i class="bi bi-lock-fill me-1"></i>Admin
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
