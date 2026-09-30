/* ============================================================
   assets/js/main.js — JavaScript público Devioz Proyectos
   ============================================================ */

'use strict';

// ============================================================
// NAVBAR — Efecto scroll
// ============================================================
(function () {
    const nav = document.getElementById('mainNav');
    if (!nav) return;

    function onScroll() {
        nav.classList.toggle('scrolled', window.scrollY > 50);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

// ============================================================
// COUNTERS — Animación de números en la sección de estadísticas
// Busca elementos con data-target="N" y los anima
// ============================================================
(function () {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);

            const el     = entry.target;
            const target = parseInt(el.getAttribute('data-counter'), 10);
            const suffix = el.getAttribute('data-suffix') || '';
            const duration = 1500;
            const start    = performance.now();

            function update(now) {
                const elapsed  = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased    = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
                el.textContent = Math.round(eased * target) + suffix;
                if (progress < 1) requestAnimationFrame(update);
            }

            requestAnimationFrame(update);
        });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));
})();

// ============================================================
// FADE IN UP — Animación de entrada al hacer scroll
// ============================================================
(function () {
    const els = document.querySelectorAll('.animate-on-scroll');
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    els.forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
})();

// ============================================================
// CONTACT FORM — Validación cliente y envío real vía fetch
// Aplica solo en contacto.php donde exista #contactForm
// ============================================================
(function () {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const btn      = form.querySelector('[type="submit"]');
    const feedback = document.getElementById('formFeedback');

    // Validación en tiempo real
    form.querySelectorAll('input, textarea, select').forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) validateField(field);
        });
    });

    function validateField(field) {
        const val = field.value.trim();
        let valid = true;
        let msg   = '';

        if (field.hasAttribute('required') && !val) {
            valid = false;
            msg   = 'Este campo es obligatorio.';
        } else if (field.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            valid = false;
            msg   = 'Ingresa un correo electrónico válido.';
        } else if (field.name === 'telefono' && val && !/^\+?[\d\s\-()]{7,15}$/.test(val)) {
            valid = false;
            msg   = 'Ingresa un teléfono válido.';
        } else if (field.name === 'mensaje' && val.length < 20 && val) {
            valid = false;
            msg   = 'El mensaje debe tener al menos 20 caracteres.';
        }

        const errorEl = field.parentElement.querySelector('.form-error, .invalid-feedback');
        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid && val !== '');

        if (errorEl) errorEl.textContent = msg;

        return valid;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Validar todos los campos
        let allValid = true;
        form.querySelectorAll('input, textarea, select').forEach(field => {
            if (!validateField(field)) allValid = false;
        });

        if (!allValid) return;

        // Deshabilitar botón mientras se procesa
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-custom me-2"></span>Enviando...';

        try {
            const response = await fetch(form.action || 'contacto.php', {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body:    new FormData(form)
            });

            const data = await response.json();

            if (data.success) {
                showFeedback('success', '<i class="bi bi-check-circle-fill me-2"></i>' + data.mensaje);
                form.reset();
                form.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
            } else {
                showFeedback('danger', '<i class="bi bi-exclamation-circle-fill me-2"></i>' + (data.mensaje || 'Ocurrió un error. Inténtalo nuevamente.'));
            }
        } catch (err) {
            showFeedback('danger', '<i class="bi bi-exclamation-circle-fill me-2"></i>Error de conexión. Verifica tu internet.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Enviar mensaje';
        }
    });

    function showFeedback(type, html) {
        if (!feedback) return;
        feedback.className = `alert-custom-${type} mt-3`;
        feedback.innerHTML = html;
        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(() => { feedback.className = ''; feedback.innerHTML = ''; }, 8000);
    }
})();

// ============================================================
// FILTER CLIENTS — Filtrado GET por sector y búsqueda
// ============================================================
(function () {
    const sectorSelect = document.getElementById('filterSector');
    const searchInput  = document.getElementById('searchCliente');
    const applyBtn     = document.getElementById('btnFiltrar');

    if (!sectorSelect && !searchInput) return;

    function applyFilters() {
        const params = new URLSearchParams(window.location.search);
        if (sectorSelect) {
            const val = sectorSelect.value;
            val ? params.set('sector', val) : params.delete('sector');
        }
        if (searchInput) {
            const val = searchInput.value.trim();
            val ? params.set('q', val) : params.delete('q');
        }
        params.delete('page'); // reset page on filter change
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', applyFilters);
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') applyFilters(); });
    }

    if (sectorSelect) {
        sectorSelect.addEventListener('change', applyFilters);
    }
})();

// ============================================================
// FILTER PROJECTS — Filtro por estado
// ============================================================
(function () {
    const estadoBtns = document.querySelectorAll('[data-filter-estado]');
    if (!estadoBtns.length) return;

    estadoBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const estado = btn.getAttribute('data-filter-estado');
            const params = new URLSearchParams(window.location.search);
            estado ? params.set('estado', estado) : params.delete('estado');
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    });
})();

// ============================================================
// TECH TAGS — Renderiza chips a partir de string separado por comas
// ============================================================
(function () {
    document.querySelectorAll('[data-tech-tags]').forEach(el => {
        const tags = el.getAttribute('data-tech-tags').split(',').map(t => t.trim()).filter(Boolean);
        el.innerHTML = tags.map(t => `<span class="tech-tag">${escapeHtml(t)}</span>`).join('');
    });
})();

// Escapa HTML para prevenir XSS en salidas JS
function escapeHtml(str) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(str).replace(/[&<>"']/g, m => map[m]);
}
