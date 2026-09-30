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
    <div style="font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1a1a2e; width: 800px; height: 1123px; margin: 0; background: #fff; position: relative; overflow: hidden; box-sizing: border-box;">
        
        <!-- Background watermark -->
        <div style="position: absolute; top: 300px; left: 100px; opacity: 0.02; pointer-events: none;">
            <svg width="600" height="600" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="none" stroke="#6C63FF" stroke-width="5"/>
                <path d="M 30 50 L 50 30 L 70 50 L 50 70 Z" fill="#6C63FF"/>
            </svg>
        </div>

        <!-- Header -->
        <div style="padding: 50px 60px 40px; border-bottom: 2px solid #F0F0F5; display: flex; justify-content: space-between; align-items: flex-start;">
            <!-- Top Left: LOGO -->
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 55px; height: 55px; background: linear-gradient(135deg, #6C63FF, #5A52D5); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 15px rgba(108, 99, 255, 0.2);">
                    <!-- SVG Logo Icon -->
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div>
                    <h1 style="margin: 0; font-size: 26px; font-weight: 900; color: #0D0D1A; letter-spacing: -0.5px;">Devioz <span style="color: #6C63FF;">Proyectos</span></h1>
                    <p style="margin: 2px 0 0; color: #6B6B85; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Agencia de Software</p>
                </div>
            </div>
            
            <!-- Top Right: Document Info -->
            <div style="text-align: right;">
                <h2 style="margin: 0 0 5px 0; font-size: 28px; color: #1A1A2E; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Propuesta</h2>
                <div style="display: inline-block; background: #F4F4F9; padding: 6px 12px; border-radius: 6px; border: 1px solid #E5E5E5;">
                    <span style="font-size: 11px; color: #6B6B85; text-transform: uppercase; font-weight: 700; margin-right: 10px;">Ref:</span>
                    <span style="font-size: 13px; color: #6C63FF; font-weight: 800;" id="pdfRef"><?= date('Y-m-d-Hi') ?></span>
                </div>
            </div>
        </div>

        <div style="padding: 40px 60px;">
            <!-- Client & Meta Info -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 50px;">
                <!-- Client Info -->
                <div style="width: 55%;">
                    <h4 style="margin: 0 0 12px; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; border-bottom: 2px solid #6C63FF; padding-bottom: 6px; display: inline-block;">Preparado Para</h4>
                    <h2 style="margin: 0 0 5px; font-size: 20px; color: #0D0D1A; font-weight: 800;" id="pdfEmpresa"></h2>
                    <p style="margin: 0 0 3px; font-size: 13px; color: #333; font-weight: 600;">Atn: <span id="pdfNombre"></span></p>
                    <p style="margin: 0; font-size: 13px; color: #666;" id="pdfCorreoSpan"></p>
                </div>
                
                <!-- Meta Data -->
                <div style="width: 40%;">
                    <h4 style="margin: 0 0 12px; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; border-bottom: 2px solid #E5E5E5; padding-bottom: 6px; display: inline-block;">Detalles del Documento</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 4px 0; font-size: 12px; color: #6B6B85; font-weight: 600;">Fecha Emisión:</td>
                            <td style="padding: 4px 0; font-size: 12px; color: #1A1A2E; text-align: right; font-weight: 700;"><?= date('d M Y') ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; font-size: 12px; color: #6B6B85; font-weight: 600;">Validez Oferta:</td>
                            <td style="padding: 4px 0; font-size: 12px; color: #1A1A2E; text-align: right; font-weight: 700;">15 Días</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; font-size: 12px; color: #6B6B85; font-weight: 600;">Elaborado por:</td>
                            <td style="padding: 4px 0; font-size: 12px; color: #1A1A2E; text-align: right; font-weight: 700;">Dpto. Comercial</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Services Table -->
            <div style="margin-bottom: 40px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="background: #1A1A2E; color: #fff; padding: 12px 20px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border-radius: 6px 0 0 6px; width: 5%;">N°</th>
                            <th style="background: #1A1A2E; color: #fff; padding: 12px 20px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Descripción del Servicio o Solución Tecnológica</th>
                            <th style="background: #1A1A2E; color: #fff; padding: 12px 20px; text-align: center; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border-radius: 0 6px 6px 0; width: 15%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="pdfServicios">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Total Block -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 60px;">
                <div style="width: 50%; background: #FAFAFC; border: 1px solid #E5E5E5; border-radius: 8px; overflow: hidden;">
                    <div style="padding: 20px; border-bottom: 1px solid #E5E5E5;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="font-size: 13px; color: #6B6B85; font-weight: 600;">Subtotal Base</td>
                                <td style="font-size: 13px; color: #1A1A2E; text-align: right; font-weight: 700;">Según Tarifa</td>
                            </tr>
                            <tr>
                                <td style="font-size: 13px; color: #6B6B85; font-weight: 600; padding-top: 8px;">Ajuste por Complejidad</td>
                                <td style="font-size: 13px; color: #1A1A2E; text-align: right; font-weight: 700; padding-top: 8px;">Incluido</td>
                            </tr>
                        </table>
                    </div>
                    <div style="padding: 20px; background: rgba(108, 99, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 14px; color: #6C63FF; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Inversión Estimada</div>
                        <div style="font-size: 28px; color: #6C63FF; font-weight: 900; letter-spacing: -1px;" id="pdfTotal"></div>
                    </div>
                </div>
            </div>

            <!-- Signatures Section -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 40px; padding: 0 20px;">
                <div style="width: 40%; text-align: center;">
                    <div style="border-bottom: 1px solid #333; height: 60px; margin-bottom: 10px; position: relative;">
                        <!-- Firma falsa o vacia -->
                        <div style="position: absolute; bottom: 5px; left: 0; right: 0; text-align: center; font-family: 'Brush Script MT', cursive; font-size: 24px; color: #000; opacity: 0.7;">
                            Aprobado
                        </div>
                    </div>
                    <p style="margin: 0; font-size: 12px; font-weight: 700; color: #1A1A2E;">Dpto. Comercial</p>
                    <p style="margin: 2px 0 0; font-size: 11px; color: #6B6B85;">Devioz Proyectos</p>
                </div>
                <div style="width: 40%; text-align: center;">
                    <div style="border-bottom: 1px solid #333; height: 60px; margin-bottom: 10px;"></div>
                    <p style="margin: 0; font-size: 12px; font-weight: 700; color: #1A1A2E;">Firma del Cliente</p>
                    <p style="margin: 2px 0 0; font-size: 11px; color: #6B6B85;">Aceptación de la Propuesta</p>
                </div>
            </div>

            <!-- Notes -->
            <div style="background: #F8F9FA; padding: 20px; border-left: 3px solid #FFB020; border-radius: 4px;">
                <h4 style="margin: 0 0 8px; font-size: 11px; color: #1A1A2E; text-transform: uppercase; font-weight: 800;"><i class="bi bi-info-circle"></i> Condiciones Comerciales Previas</h4>
                <p style="margin: 0; font-size: 10px; color: #666; line-height: 1.6; text-align: justify;">
                    El monto presentado es una <strong>estimación referencial</strong> basada en tarifas estándar. No constituye un contrato vinculante. Los costos finales se definirán en un anexo técnico detallado tras la reunión de levantamiento de requerimientos. Los precios expresados están en dólares americanos (USD) y no incluyen impuestos de ley vigentes a menos que se indique explícitamente.
                </p>
            </div>
        </div>

        <!-- Absolute Footer -->
        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 20px 60px; background: #0D0D1A; border-top: 4px solid #6C63FF; display: flex; justify-content: space-between; align-items: center; color: #A0A0B8; font-size: 11px; font-weight: 500;">
            <div>
                <span style="color: #fff;">Web:</span> www.devioz.com
            </div>
            <div>
                <span style="color: #fff;">Email:</span> contacto@devioz.com
            </div>
            <div>
                <span style="color: #fff;">RUC:</span> 20611991909
            </div>
            <div style="text-align: right;">
                Página 1 de 1
            </div>
        </div>

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
    
    document.getElementById('pdfCorreoSpan').innerText = this.correo.value;
    
    let listaServicios = document.getElementById('pdfServicios');
    listaServicios.innerHTML = '';
    let serviciosArray = [];
    document.querySelectorAll('input[name="servicios[]"]:checked').forEach((el, index) => {
        const bg = index % 2 === 0 ? '#FFFFFF' : '#FAFAFC';
        listaServicios.innerHTML += `
            <tr style="background: ${bg}; border-bottom: 1px solid #E5E5E5;">
                <td style="padding: 12px 20px; font-size: 13px; color: #6B6B85; font-weight: 700; width: 5%;">${String(index + 1).padStart(2, '0')}</td>
                <td style="padding: 12px 20px; font-size: 13px; color: #1A1A2E; font-weight: 600;">${el.value}</td>
                <td style="padding: 12px 20px; font-size: 12px; color: #00D4AA; font-weight: 700; text-align: center; width: 15%;"><span style="background: rgba(0,212,170,0.1); padding: 4px 8px; border-radius: 4px;">INCLUIDO</span></td>
            </tr>`;
        serviciosArray.push(el.value);
    });

    // Generar PDF y obtener Base64
    const element = document.getElementById('pdfTemplate');
    element.classList.remove('d-none'); // Mostrar temporalmente para el renderizado
    
    const opt = {
      margin:       0,
      filename:     `Propuesta_Devioz_${this.empresa.value.replace(/\s+/g, '_')}.pdf`,
      image:        { type: 'jpeg', quality: 1 },
      html2canvas:  { scale: 2, useCORS: true, logging: false },
      jsPDF:        { unit: 'px', format: [800, 1123], orientation: 'portrait' }
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
