<script>
    // Theme Pre-load (Senior Pro — Inmediato, sin parpadeo y sincronizado en todo el panel)
    (function() {
        var savedTheme = localStorage.getItem('admin_theme') || localStorage.getItem('theme');
        if (!savedTheme) {
            var match = document.cookie.match(/(?:^|; )admin_theme=([^;]*)/);
            if (match) savedTheme = decodeURIComponent(match[1]);
        }
        if (savedTheme === 'light') {
            document.documentElement.classList.add('light-mode');
            if (document.body) {
                document.body.classList.add('light-mode');
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    document.body && document.body.classList.add('light-mode');
                });
            }
        } else if (savedTheme === 'dark') {
            document.documentElement.classList.remove('light-mode');
            if (document.body) document.body.classList.remove('light-mode');
        }
    })();
</script>
<style>
/* ==========================================================
   GLOBAL ADMIN LIGHT MODE ENGINE (SENIOR PRO — TODAS LAS VISTAS)
   Aplica a Clientes, Proyectos, Logros, Servicios, Contactos,
   Cotizaciones, Usuarios, Auditoría y Dashboard
   ========================================================== */

/* Variables Base y Fondo Global */
html.light-mode,
body.light-mode {
    --admin-bg:        #F1F5F9 !important;
    --admin-sidebar:   #FFFFFF !important;
    --admin-card:      #FFFFFF !important;
    --admin-card-2:    #F8FAFC !important;
    --admin-border:    #E2E8F0 !important;
    --admin-hover:     rgba(108, 99, 255, 0.08) !important;
    --admin-active:    rgba(108, 99, 255, 0.15) !important;
    --text-primary:    #0F172A !important;
    --text-secondary:  #334155 !important;
    --text-muted:      #64748B !important;
    background-color:  #F1F5F9 !important;
    color:             #0F172A !important;
}

body.light-mode .admin-layout,
body.light-mode .admin-main,
body.light-mode .admin-page {
    background-color: #F1F5F9 !important;
    color: #0F172A !important;
}

/* Sidebar en Modo Claro */
body.light-mode .admin-sidebar {
    background: #FFFFFF !important;
    border-right: 1px solid #E2E8F0 !important;
    box-shadow: 2px 0 16px rgba(0,0,0,0.03) !important;
}
body.light-mode .sidebar-brand a {
    color: #0F172A !important;
}
body.light-mode .sidebar-label {
    color: #64748B !important;
}
body.light-mode .sidebar-nav .nav-link {
    color: #334155 !important;
}
body.light-mode .sidebar-nav .nav-link:hover {
    color: #6C63FF !important;
    background: rgba(108, 99, 255, 0.08) !important;
}
body.light-mode .sidebar-nav .nav-link.active {
    background: linear-gradient(135deg, rgba(108, 99, 255, 0.12), rgba(108, 99, 255, 0.2)) !important;
    color: #6C63FF !important;
    font-weight: 700 !important;
}
body.light-mode .sidebar-footer {
    border-top: 1px solid #E2E8F0 !important;
    background: #FFFFFF !important;
}
body.light-mode .sidebar-user .name {
    color: #0F172A !important;
}
body.light-mode .sidebar-user .role {
    color: #64748B !important;
}
body.light-mode .btn-logout {
    background: rgba(255, 77, 106, 0.1) !important;
    color: #FF4D6A !important;
}

/* Topbar en Modo Claro */
body.light-mode .admin-topbar {
    background: #FFFFFF !important;
    border-bottom: 1px solid #E2E8F0 !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03) !important;
}
body.light-mode .topbar-title {
    color: #0F172A !important;
}
body.light-mode .topbar-btn {
    border-color: #E2E8F0 !important;
    color: #334155 !important;
    background: #F8FAFC !important;
}
body.light-mode .topbar-btn:hover {
    background: rgba(108, 99, 255, 0.08) !important;
    color: #6C63FF !important;
}

/* Tarjetas y Paneles (Clientes, Proyectos, Logros, Servicios, Cotizaciones, etc.) */
body.light-mode .admin-card,
body.light-mode .stat-card {
    background: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
}
body.light-mode .admin-card-header {
    background: #FFFFFF !important;
    border-bottom: 1px solid #E2E8F0 !important;
}
body.light-mode .admin-card-title {
    color: #0F172A !important;
}
body.light-mode .admin-card-body {
    background: #FFFFFF !important;
    color: #334155 !important;
}
body.light-mode .admin-card-footer {
    background: #FFFFFF !important;
    border-top: 1px solid #E2E8F0 !important;
}
body.light-mode .stat-card-value {
    color: #0F172A !important;
}
body.light-mode .stat-card-label {
    color: #64748B !important;
}

/* Títulos y Subtítulos de Páginas */
body.light-mode .admin-page-title {
    color: #0F172A !important;
}
body.light-mode .admin-page-subtitle {
    color: #64748B !important;
}
body.light-mode .breadcrumb-admin li,
body.light-mode .breadcrumb-admin a {
    color: #64748B !important;
}

/* Tablas Admin en Modo Claro */
body.light-mode .table-admin {
    background: #FFFFFF !important;
}
body.light-mode .table-admin thead th {
    background: #F8FAFC !important;
    color: #475569 !important;
    border-bottom: 1px solid #E2E8F0 !important;
}
body.light-mode .table-admin tbody tr {
    border-bottom: 1px solid #E2E8F0 !important;
    background: transparent !important;
}
body.light-mode .table-admin tbody tr:hover td {
    background: rgba(108, 99, 255, 0.03) !important;
}
body.light-mode .table-admin td {
    color: #334155 !important;
    border-bottom: 1px solid #E2E8F0 !important;
}
body.light-mode .table-admin .text-white,
body.light-mode .table-admin p {
    color: #0F172A !important;
}

/* Formularios, Inputs y Filtros */
body.light-mode .form-control-admin,
body.light-mode .form-select-admin {
    background: #FFFFFF !important;
    border: 1px solid #CBD5E1 !important;
    color: #0F172A !important;
}
body.light-mode .form-control-admin:focus,
body.light-mode .form-select-admin:focus {
    border-color: #6C63FF !important;
    background: #FFFFFF !important;
    box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.15) !important;
    color: #0F172A !important;
}
body.light-mode .form-label-admin {
    color: #334155 !important;
}

/* DataTables en Clientes, Proyectos, etc. */
body.light-mode div.dataTables_wrapper div.dataTables_length select,
body.light-mode div.dataTables_wrapper div.dataTables_filter input {
    background-color: #FFFFFF !important;
    border: 1px solid #CBD5E1 !important;
    color: #0F172A !important;
}
body.light-mode div.dataTables_wrapper div.dataTables_length label,
body.light-mode div.dataTables_wrapper div.dataTables_filter label,
body.light-mode div.dataTables_wrapper div.dataTables_info,
body.light-mode div.dataTables_wrapper div.dataTables_paginate {
    color: #64748B !important;
}
body.light-mode div.dt-buttons .btn {
    background-color: #FFFFFF !important;
    border: 1px solid #CBD5E1 !important;
    color: #334155 !important;
}
body.light-mode div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item .page-link {
    background-color: #FFFFFF !important;
    border: 1px solid #E2E8F0 !important;
    color: #334155 !important;
}
body.light-mode div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item.active .page-link {
    background-color: #6C63FF !important;
    border-color: #6C63FF !important;
    color: #FFFFFF !important;
}
body.light-mode div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-item.disabled .page-link {
    background-color: #F8FAFC !important;
    border-color: #E2E8F0 !important;
    color: #94A3B8 !important;
}

/* Botones Secundarios & Empty State */
body.light-mode .btn-admin-secondary {
    background: #F8FAFC !important;
    border-color: #CBD5E1 !important;
    color: #334155 !important;
}
body.light-mode .btn-admin-secondary:hover {
    background: #E2E8F0 !important;
    color: #0F172A !important;
}
body.light-mode .empty-state {
    color: #64748B !important;
}
body.light-mode .empty-state h5 {
    color: #0F172A !important;
}
body.light-mode .empty-state p {
    color: #64748B !important;
}

/* ==========================================================
   ANIMATED TOGGLE SWITCH (EXACTO AL PANEL PÚBLICO)
   ========================================================== */
.theme-switch-wrapper {
    display: inline-flex !important;
    align-items: center !important;
}
.theme-switch {
    position: relative !important;
    width: 52px !important;
    height: 28px !important;
    background-color: #1A1A2E !important;
    border-radius: 50px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0 6px !important;
    transition: background-color 0.4s ease, border-color 0.4s ease !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-sizing: border-box !important;
    user-select: none !important;
    margin: 0 !important;
}
.theme-switch i {
    font-size: 12px !important;
    z-index: 1 !important;
    pointer-events: none !important;
    line-height: 1 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 14px !important;
    height: 14px !important;
}
.theme-switch .bi-moon-fill { color: #8B85FF !important; }
.theme-switch .bi-sun-fill { color: #FFB020 !important; }

.theme-switch-indicator {
    position: absolute !important;
    top: 2px !important;
    left: 2px !important;
    width: 22px !important;
    height: 22px !important;
    background-color: #FFFFFF !important;
    border-radius: 50% !important;
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) !important;
    z-index: 0 !important;
    pointer-events: none !important;
}

/* Estado Claro para el Switch */
body.light-mode .theme-switch,
html.light-mode .theme-switch {
    background-color: #E9ECEF !important;
    border-color: rgba(0, 0, 0, 0.1) !important;
}
body.light-mode .theme-switch-indicator,
html.light-mode .theme-switch-indicator {
    transform: translateX(24px) !important;
    background-color: #FFFFFF !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18) !important;
}
</style>
<script>
    // Theme Switch Controller (Controlador Unificado Senior Pro — Sin conflictos ni doble rebote)
    (function() {
        var _themeDebounce = 0;
        window.toggleAdminTheme = function() {
            var now = Date.now();
            if (now - _themeDebounce < 250) return;
            _themeDebounce = now;

            var isCurrentlyLight = document.body.classList.contains('light-mode') || document.documentElement.classList.contains('light-mode');
            var targetTheme = isCurrentlyLight ? 'dark' : 'light';

            if (targetTheme === 'light') {
                document.body.classList.add('light-mode');
                document.documentElement.classList.add('light-mode');
            } else {
                document.body.classList.remove('light-mode');
                document.documentElement.classList.remove('light-mode');
            }

            localStorage.setItem('admin_theme', targetTheme);
            localStorage.setItem('theme', targetTheme);
            document.cookie = "admin_theme=" + targetTheme + "; path=/; max-age=31536000";

            if (typeof showToast === 'function') {
                showToast(targetTheme === 'light' ? 'Modo Claro activado' : 'Modo Oscuro activado', 'info');
            }
        };

        if (!window.__adminThemeListenerBound) {
            window.__adminThemeListenerBound = true;
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('#adminThemeToggle, #themeToggle, .theme-switch');
                if (btn) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.toggleAdminTheme();
                }
            });
        }
    })();
</script>
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
            <?= sidebarItem('/Proyecto_Servicios/admin/auditoria/listar.php', 'bi-shield-check', 'Auditoría', $activePage, 'auditoria') ?>
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
