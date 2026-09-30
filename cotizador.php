<?php
// cotizador.php
$pageTitle = 'Cotizador Online | Devioz Proyectos';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/config/database.php';

// Obtener servicios activos
$db = getDB();
$stmt = $db->query("SELECT id_servicio, nombre, descripcion, precio_base FROM servicios WHERE estado = 1");
$servicios = $stmt->fetchAll();
?>

<section class="page-content py-5">
    <div class="container">
        <div class="row mb-5 text-center">
            <div class="col-12 fade-in-up">
                <span class="section-badge">Motor Inteligente</span>
                <h1 class="section-title">Cotizador <span class="gradient-text">Online</span></h1>
                <p class="section-subtitle">Estima el costo de tu proyecto tecnológico al instante y obtén una propuesta formal en PDF.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8 fade-in-up delay-1">
                <div class="card-glass border-0">
                    <div class="card-body p-2 p-md-4">
                    <form id="cotizadorForm">
                        
                        <!-- Paso 1: Datos Básicos -->
                        <div class="step" id="step1">
                            <h4 class="mb-4 text-primary-custom"><i class="bi bi-person-badge me-2"></i>1. Datos de Contacto</h4>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary-custom fw-bold">Nombre Completo *</label>
                                    <input type="text" class="input-dark" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary-custom fw-bold">Empresa *</label>
                                    <input type="text" class="input-dark" name="empresa" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary-custom fw-bold">Correo Electrónico *</label>
                                    <input type="email" class="input-dark" name="correo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary-custom fw-bold">Teléfono</label>
                                    <input type="text" class="input-dark" name="telefono">
                                </div>
                            </div>
                            <div class="text-end mt-5">
                                <button type="button" class="btn-accent px-5 py-2" onclick="nextStep(2)">Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Paso 2: Selección de Servicios -->
                        <div class="step d-none" id="step2">
                            <h4 class="mb-4 text-primary-custom"><i class="bi bi-cpu me-2"></i>2. ¿Qué servicios necesitas?</h4>
                            <div class="row g-3">
                                <?php foreach ($servicios as $s): ?>
                                <div class="col-md-6">
                                    <div class="form-check custom-checkbox-card h-100 p-3 rounded-custom position-relative">
                                        <input class="form-check-input position-absolute top-0 end-0 m-3" type="checkbox" name="servicios[]" value="<?= htmlspecialchars($s['nombre']) ?>" data-precio="<?= $s['precio_base'] ?>" id="srv_<?= $s['id_servicio'] ?>">
                                        <label class="form-check-label w-100 stretched-link" for="srv_<?= $s['id_servicio'] ?>">
                                            <h6 class="fw-bold mb-1 text-text-primary"><?= htmlspecialchars($s['nombre']) ?></h6>
                                            <p class="small text-muted-custom mb-0"><?= htmlspecialchars(mb_strimwidth($s['descripcion'], 0, 80, '...')) ?></p>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn-outline-custom" onclick="nextStep(1)"><i class="bi bi-arrow-left me-2"></i> Atrás</button>
                                <button type="button" class="btn-accent px-5" onclick="nextStep(3)">Siguiente <i class="bi bi-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Paso 3: Detalles y Estimación -->
                        <div class="step d-none" id="step3">
                            <h4 class="mb-4 text-primary-custom"><i class="bi bi-speedometer2 me-2"></i>3. Complejidad del Proyecto</h4>
                            
                            <div class="mb-4">
                                <label class="form-label text-secondary-custom fw-bold">Tamaño de la Empresa</label>
                                <select class="input-dark" name="tamanio" id="tamanio">
                                    <option value="1">Micro (1-10 empleados) - Base</option>
                                    <option value="1.5">Pyme (11-50 empleados) - +50%</option>
                                    <option value="2">Mediana (51-200 empleados) - +100%</option>
                                    <option value="3">Corporativa (+200 empleados) - +200%</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-secondary-custom fw-bold">Urgencia del Proyecto</label>
                                <select class="input-dark" name="urgencia" id="urgencia">
                                    <option value="1">Estándar (Planificación normal)</option>
                                    <option value="1.2">Alta Urgencia - +20% (Prioridad)</option>
                                </select>
                            </div>
                            
                            <div class="mt-5 p-4 rounded-lg-custom border-custom text-center" style="background: rgba(108, 99, 255, 0.1);">
                                <h5 class="fw-bold text-primary-custom mb-3"><i class="bi bi-calculator me-2"></i>Presupuesto Estimado</h5>
                                <h2 class="display-4 fw-bold gradient-text mb-0" id="totalEstimado">$0.00 <span class="fs-5 text-secondary-custom fw-normal">USD (Aprox)</span></h2>
                                <p class="mb-0 mt-3 small text-muted-custom">* Esta es una estimación referencial basada en tarifas estándar. No constituye un contrato vinculante.</p>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn-outline-custom" onclick="nextStep(2)"><i class="bi bi-arrow-left me-2"></i> Atrás</button>
                                <button type="submit" class="btn-accent px-4" id="btnGenerar">
                                    <i class="bi bi-file-earmark-pdf me-2"></i>Generar y Enviar Propuesta
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contenedor oculto para armar el PDF -->
<div id="pdfTemplate" class="d-none">
    <div style="padding: 40px; font-family: 'Helvetica', 'Arial', sans-serif; color: #333;">
        <div style="border-bottom: 2px solid #0d6efd; padding-bottom: 20px; margin-bottom: 30px;">
            <h1 style="color: #0d6efd; margin:0;">Devioz Proyectos</h1>
            <p style="margin:5px 0 0 0; color: #666;">Propuesta Técnica Preliminar</p>
        </div>
        
        <h3 style="margin-bottom: 10px;">Datos del Cliente</h3>
        <p><strong>Empresa:</strong> <span id="pdfEmpresa"></span><br>
        <strong>Contacto:</strong> <span id="pdfNombre"></span><br>
        <strong>Fecha:</strong> <?= date('d/m/Y') ?></p>

        <h3 style="margin-top: 30px; margin-bottom: 10px;">Servicios Solicitados</h3>
        <ul id="pdfServicios" style="line-height: 1.8;"></ul>

        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 40px; text-align: center;">
            <h4 style="margin: 0 0 10px 0; color: #555;">Inversión Estimada</h4>
            <h2 style="margin: 0; color: #0d6efd; font-size: 32px;" id="pdfTotal"></h2>
        </div>
        
        <p style="font-size: 11px; color: #999; margin-top: 50px; text-align: justify;">
            <strong>Nota Legal:</strong> El presente documento ha sido generado automáticamente de forma preliminar y no representa un compromiso contractual por parte de Devioz S.A.C. (RUC: 20611991909). Nuestro equipo comercial se pondrá en contacto a la brevedad para afinar los detalles técnicos y emitir una propuesta final formal.
        </p>
    </div>
</div>

<!-- Incluir libreria para PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
.custom-checkbox-card { 
    background: var(--dark-3); 
    border: 1px solid var(--glass-border); 
    cursor: pointer; 
    transition: var(--transition); 
}
.custom-checkbox-card:hover { 
    border-color: var(--primary) !important; 
    transform: translateY(-2px); 
    box-shadow: 0 4px 15px rgba(108, 99, 255, 0.2);
}
.custom-checkbox-card input:checked ~ label h6 { color: var(--primary-light) !important; }
.custom-checkbox-card input:checked { background-color: var(--primary); border-color: var(--primary); }
</style>

<script>
let totalCalculado = 0;

function nextStep(step) {
    // Basic validation for step 1
    if (step === 2) {
        let form = document.getElementById('cotizadorForm');
        if(!form.nombre.value || !form.empresa.value || !form.correo.value) {
            alert("Por favor completa los campos obligatorios (*).");
            return;
        }
    }
    if (step === 3) {
        let selected = document.querySelectorAll('input[name="servicios[]"]:checked');
        if (selected.length === 0) {
            alert("Por favor selecciona al menos un servicio.");
            return;
        }
        calcularTotal();
    }

    document.querySelectorAll('.step').forEach(el => el.classList.add('d-none'));
    document.getElementById('step' + step).classList.remove('d-none');
}

function calcularTotal() {
    let subtotal = 0;
    document.querySelectorAll('input[name="servicios[]"]:checked').forEach(el => {
        subtotal += parseFloat(el.getAttribute('data-precio') || 0);
    });

    let multiplicadorTamanio = parseFloat(document.getElementById('tamanio').value);
    let multiplicadorUrgencia = parseFloat(document.getElementById('urgencia').value);

    totalCalculado = subtotal * multiplicadorTamanio * multiplicadorUrgencia;
    
    document.getElementById('totalEstimado').innerHTML = `$${totalCalculado.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span class="fs-6 text-muted fw-normal">USD (Aprox)</span>`;
}

// Recalcular al cambiar selects en paso 3
document.getElementById('tamanio').addEventListener('change', calcularTotal);
document.getElementById('urgencia').addEventListener('change', calcularTotal);

document.getElementById('cotizadorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnGenerar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';

    // Rellenar datos en el template del PDF
    document.getElementById('pdfEmpresa').innerText = this.empresa.value;
    document.getElementById('pdfNombre').innerText = this.nombre.value;
    document.getElementById('pdfTotal').innerText = `$${totalCalculado.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} USD`;
    
    let listaServicios = document.getElementById('pdfServicios');
    listaServicios.innerHTML = '';
    let serviciosArray = [];
    document.querySelectorAll('input[name="servicios[]"]:checked').forEach(el => {
        listaServicios.innerHTML += `<li>${el.value}</li>`;
        serviciosArray.push(el.value);
    });

    // Generar PDF y obtener Base64
    const element = document.getElementById('pdfTemplate');
    element.classList.remove('d-none'); // Mostrar temporalmente para el renderizado
    
    const opt = {
      margin:       1,
      filename:     `Propuesta_Devioz_${this.empresa.value.replace(/\s+/g, '_')}.pdf`,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2 },
      jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    try {
        // Descargar al usuario
        await html2pdf().set(opt).from(element).save();

        // Enviar datos al servidor
        const formData = {
            nombre: this.nombre.value,
            correo: this.correo.value,
            telefono: this.telefono.value,
            empresa: this.empresa.value,
            servicios: serviciosArray,
            total: totalCalculado
        };

        const response = await fetch('/Proyecto_Servicios/guardar-cotizacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            element.innerHTML = `
                <div class="text-center py-5 fade-in-up">
                    <i class="bi bi-check-circle-fill" style="font-size: 4rem; color: var(--success);"></i>
                    <h2 class="mt-4 text-primary-custom fw-bold">¡Propuesta Generada!</h2>
                    <p class="lead text-secondary-custom">El documento PDF se ha descargado en tu dispositivo.</p>
                    <p class="text-muted-custom">Un consultor de Devioz se pondrá en contacto contigo pronto.</p>
                    <a href="/Proyecto_Servicios/" class="btn-accent px-4 py-2 mt-3 d-inline-block text-decoration-none">Volver al Inicio</a>
                </div>
            `;
            document.getElementById('step3').innerHTML = element.innerHTML;
        } else {
            alert('Error al guardar la cotización: ' + (result.message || 'Error desconocido'));
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-2"></i>Generar y Enviar Propuesta';
        }
    } catch(err) {
        console.error(err);
        alert('Hubo un error al generar la cotización.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-2"></i>Generar y Enviar Propuesta';
    } finally {
        element.classList.add('d-none');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
