<?php
// ============================================================
// includes/admin-sidebar.php — Sidebar del panel administrativo
// Variables esperadas: $activePage (string con nombre del módulo)
// ============================================================
if (!isset($activePage)) $activePage = '';

// Calcular base relativa al admin
$adminBase = '/Proyecto_Servicios/admin/';

function sidebarItem(string $href, string $icon, string $label, string $active, string $key): string {
    $cls = ($active === $key) ? 'active' : '';
    return "
    <li class=\"nav-item\">
        <a class=\"nav-link {$cls}\" href=\"{$href}\">
            <i class=\"bi {$icon} me-2\"></i>{$label}
        </a>
    </li>";
}
?>
<script>
    window.csrfToken = "<?= get_csrf_token() ?>";
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll("form").forEach(form => {
            if (form.method.toLowerCase() === "post") {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "csrf_token";
                input.value = window.csrfToken;
                form.appendChild(input);
            }
        });
        
        // --- SSE: Notificaciones en Tiempo Real ---
        if (typeof EventSource !== "undefined") {
            const source = new EventSource("/Proyecto_Servicios/api/v1/sse-notificaciones.php");
            source.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.notificaciones && data.notificaciones.length > 0) {
                    data.notificaciones.forEach(notif => {
                        // Utilizar la función showToast global de admin.js
                        if(typeof showToast === 'function') {
                            const link = notif.url ? `<a href="${notif.url}" class="text-white ms-2 text-decoration-underline" style="font-size:0.8rem">Ver detalle</a>` : '';
                            showToast(`<b>NUEVA NOTIFICACIÓN:</b> ${notif.mensaje} ${link}`, 'primary');
                        }
                    });
                }
            };
            source.onerror = function(event) {
                console.log("Conexión SSE perdida o en reconexión...");
            };
        }
    });
</script>
<aside class="admin-sidebar d-flex flex-column" id="adminSidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="/Proyecto_Servicios/admin/dashboard.php">
            <i class="bi bi-layers-fill me-2"></i>
            <span>Devioz<b>.</b></span>
        </a>
        <button class="sidebar-toggle d-lg-none ms-auto" id="sidebarClose" aria-label="Cerrar sidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav flex-grow-1">
        <p class="sidebar-label">Principal</p>
        <ul class="nav flex-column">
            <?= sidebarItem('/Proyecto_Servicios/admin/dashboard.php', 'bi-speedometer2', 'Dashboard', $activePage, 'dashboard') ?>
        </ul>

        <p class="sidebar-label mt-3">Gestión</p>
        <ul class="nav flex-column">
            <?= sidebarItem('/Proyecto_Servicios/admin/clientes/listar.php', 'bi-building', 'Clientes', $activePage, 'clientes') ?>
            <?= sidebarItem('/Proyecto_Servicios/admin/proyectos/listar.php', 'bi-kanban', 'Proyectos', $activePage, 'proyectos') ?>
            <?= sidebarItem('/Proyecto_Servicios/admin/logros/listar.php', 'bi-trophy', 'Logros', $activePage, 'logros') ?>
            <?= sidebarItem('/Proyecto_Servicios/admin/servicios/listar.php', 'bi-gear', 'Servicios', $activePage, 'servicios') ?>
        </ul>

        <p class="sidebar-label mt-3">Comunicaciones y Ventas</p>
        <ul class="nav flex-column">
            <?= sidebarItem('/Proyecto_Servicios/admin/contactos/listar.php', 'bi-envelope', 'Contactos', $activePage, 'contactos') ?>
            <?= sidebarItem('/Proyecto_Servicios/admin/cotizaciones/listar.php', 'bi-file-earmark-pdf', 'Cotizaciones', $activePage, 'cotizaciones') ?>
        </ul>

        <p class="sidebar-label mt-3">Configuración</p>
        <ul class="nav flex-column">
            <?= sidebarItem('/Proyecto_Servicios/admin/usuarios/listar.php', 'bi-people', 'Usuarios', $activePage, 'usuarios') ?>
        </ul>
    </nav>

    <!-- User & logout -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($_SESSION['admin_nombre'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <p class="name"><?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?></p>
                <p class="role"><?= htmlspecialchars(ucfirst($_SESSION['admin_rol'] ?? '')) ?></p>
            </div>
        </div>
        <a href="/Proyecto_Servicios/admin/logout.php" class="btn-logout mt-2" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right me-1"></i>Salir
        </a>
    </div>
</aside>
