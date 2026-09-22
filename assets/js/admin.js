/* ============================================================
   assets/js/admin.js — JavaScript del panel administrativo
   ============================================================ */

'use strict';

// ============================================================
// SIDEBAR — Toggle mobile
// ============================================================
(function () {
    const sidebar  = document.getElementById('adminSidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const openBtn  = document.getElementById('sidebarOpen');
    const closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);
})();

// ============================================================
// DELETE CONFIRM — Modal de confirmación antes de eliminar
// Busca botones con data-delete-url y data-delete-name
// ============================================================
(function () {
    const modal    = document.getElementById('deleteModal');
    if (!modal) return;

    const modalBS  = new bootstrap.Modal(modal);
    const nameEl   = document.getElementById('deleteItemName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    let deleteUrl = '';

    document.querySelectorAll('[data-delete-url]').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteUrl = btn.getAttribute('data-delete-url');
            const name = btn.getAttribute('data-delete-name') || 'este elemento';
            if (nameEl) nameEl.textContent = name;
            modalBS.show();
        });
    });

    confirmBtn?.addEventListener('click', () => {
        if (deleteUrl) window.location.href = deleteUrl;
    });
})();

// ============================================================
// TOGGLE ESTADO — AJAX para activar/desactivar servicios/clientes
// Busca elementos con data-toggle-url, data-toggle-id, data-toggle-table, data-toggle-field
// ============================================================
(function () {
    document.querySelectorAll('[data-toggle-url]').forEach(el => {
        el.addEventListener('change', async function () {
            const url    = el.getAttribute('data-toggle-url');
            const id     = el.getAttribute('data-toggle-id');
            const table  = el.getAttribute('data-toggle-table');
            const field  = el.getAttribute('data-toggle-field') || 'estado';
            const value  = el.checked ? 1 : 0;

            try {
                const res  = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id, table, field, value })
                });
                const data = await res.json();

                if (!data.success) {
                    // Revertir el toggle si falla
                    el.checked = !el.checked;
                    showToast('Error al actualizar. Intenta de nuevo.', 'danger');
                } else {
                    showToast(data.mensaje || 'Estado actualizado.', 'success');
                    // Actualizar badge de estado si existe
                    const row  = el.closest('tr');
                    const badge = row?.querySelector('[data-estado-badge]');
                    if (badge) {
                        badge.textContent = value ? 'Activo' : 'Inactivo';
                        badge.className   = `badge-admin ${value ? 'badge-active' : 'badge-inactive'}`;
                    }
                }
            } catch (err) {
                el.checked = !el.checked;
                showToast('Error de conexión.', 'danger');
            }
        });
    });
})();

// ============================================================
// CAMBIAR ESTADO CONTACTO — AJAX
// ============================================================
(function () {
    document.querySelectorAll('[data-estado-contacto]').forEach(sel => {
        sel.addEventListener('change', async function () {
            const id    = sel.getAttribute('data-estado-contacto');
            const estado = sel.value;

            try {
                const res  = await fetch('/Proyecto_Servicios/admin/contactos/cambiar-estado.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id, estado })
                });
                const data = await res.json();
                showToast(data.success ? 'Estado actualizado.' : 'Error al actualizar.', data.success ? 'success' : 'danger');

                // Actualizar badge visual
                const row   = sel.closest('tr');
                const badge = row?.querySelector('[data-badge-estado]');
                if (badge) {
                    const map = {
                        'Nuevo':      'badge-nuevo',
                        'Revisado':   'badge-revisado',
                        'Contactado': 'badge-contactado',
                        'Cerrado':    'badge-cerrado'
                    };
                    badge.className = 'badge-admin ' + (map[estado] || '');
                    badge.textContent = estado;
                }
            } catch (err) {
                showToast('Error de conexión.', 'danger');
            }
        });
    });
})();

// ============================================================
// LOGRO — Cálculo automático de porcentaje de mejora
// ============================================================
(function () {
    const ant = document.getElementById('indicador_anterior');
    const act = document.getElementById('indicador_actual');
    const pct = document.getElementById('porcentaje_mejora');

    if (!ant || !act || !pct) return;

    function calcPct() {
        const a = parseFloat(ant.value);
        const b = parseFloat(act.value);
        if (!isNaN(a) && !isNaN(b) && a !== 0) {
            pct.value = (((b - a) / Math.abs(a)) * 100).toFixed(2);
        } else {
            pct.value = '';
        }
    }

    ant.addEventListener('input', calcPct);
    act.addEventListener('input', calcPct);
})();

// ============================================================
// FILE UPLOAD PREVIEW
// ============================================================
(function () {
    document.querySelectorAll('[data-preview-target]').forEach(input => {
        input.addEventListener('change', function () {
            const targetId = input.getAttribute('data-preview-target');
            const preview  = document.getElementById(targetId);
            if (!preview) return;

            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    });
})();

// ============================================================
// FORM VALIDATION ADMIN — Validación cliente antes de submit
// ============================================================
(function () {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;

            form.querySelectorAll('[required]').forEach(field => {
                const val = field.value.trim();
                if (!val) {
                    valid = false;
                    field.classList.add('is-invalid');
                    let err = field.parentElement.querySelector('.form-error');
                    if (!err) {
                        err = document.createElement('small');
                        err.className = 'form-error';
                        field.parentElement.appendChild(err);
                    }
                    err.textContent = 'Este campo es obligatorio.';
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });

            if (!valid) {
                e.preventDefault();
                const first = form.querySelector('.is-invalid');
                first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
})();

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
let toastContainer = null;

function showToast(message, type = 'success') {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;';
        document.body.appendChild(toastContainer);
    }

    const colors = {
        success: { bg: 'rgba(0,212,170,0.12)', border: 'rgba(0,212,170,0.3)', color: '#00D4AA', icon: 'bi-check-circle-fill' },
        danger:  { bg: 'rgba(255,77,106,0.12)', border: 'rgba(255,77,106,0.3)', color: '#FF4D6A', icon: 'bi-exclamation-circle-fill' },
        warning: { bg: 'rgba(255,176,32,0.12)', border: 'rgba(255,176,32,0.3)', color: '#FFB020', icon: 'bi-exclamation-triangle-fill' },
        info:    { bg: 'rgba(56,189,248,0.1)',  border: 'rgba(56,189,248,0.2)', color: '#38BDF8', icon: 'bi-info-circle-fill' }
    };

    const c = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.style.cssText = `background:${c.bg};border:1px solid ${c.border};color:${c.color};padding:0.75rem 1.25rem;border-radius:10px;font-size:0.85rem;font-weight:600;display:flex;align-items:center;gap:0.6rem;box-shadow:0 8px 24px rgba(0,0,0,0.4);font-family:'Inter',sans-serif;min-width:220px;max-width:360px;animation:slideInRight 0.3s ease;`;
    toast.innerHTML = `<i class="bi ${c.icon}"></i>${message}`;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Animaciones para toast
const style = document.createElement('style');
style.textContent = `
@keyframes slideInRight { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
@keyframes fadeOut { from { opacity:1; } to { opacity:0; transform:translateX(40px); } }
`;
document.head.appendChild(style);
