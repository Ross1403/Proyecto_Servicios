<?php
// admin/cotizaciones/detalles.php
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/cotizaciones/listar.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM cotizaciones WHERE id_cotizacion = :id");
$stmt->execute([':id' => $id]);
$cotizacion = $stmt->fetch();

if (!$cotizacion) {
    header('Location: /Proyecto_Servicios/admin/cotizaciones/listar.php');
    exit;
}

$servicios = json_decode($cotizacion['servicios_json'], true) ?: [];

$activePage = 'cotizaciones';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Cotización | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
    <style>
        .service-tag {
            background: rgba(108, 99, 255, 0.1);
            border: 1px solid rgba(108, 99, 255, 0.2);
            color: var(--primary-light);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
    </style>
</head>
<body>
<div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>
<main class="admin-main">
<div class="admin-topbar">
    <button class="topbar-toggle" id="sidebarOpen"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Detalle de Cotización</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/cotizaciones/listar.php">Cotizaciones</a></li>
                <li>Detalle COT-<?= $cotizacion['id_cotizacion'] ?></li>
            </ul>
            <h1 class="admin-page-title mt-2">Cotización de <?= htmlspecialchars($cotizacion['empresa']) ?></h1>
        </div>
        <div class="d-flex gap-2">
            <button id="btnDescargarPdf" class="btn-admin-success">
                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
            </button>
            <a href="mailto:<?= htmlspecialchars($cotizacion['correo']) ?>" class="btn-admin-primary">
                <i class="bi bi-envelope"></i> Contactar
            </a>
            <a href="/Proyecto_Servicios/admin/cotizaciones/listar.php" class="btn-admin-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna 1: Info del cliente y presupuesto -->
        <div class="col-lg-8">
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="bi bi-person-lines-fill me-2"></i>Información del Solicitante</h2>
                </div>
                <div class="admin-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label-admin">Empresa / Organización</label>
                            <p class="mb-0" style="font-weight: 600; font-size: 1.1rem; color: var(--text-primary);"><?= htmlspecialchars($cotizacion['empresa']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-admin">Nombre de Contacto</label>
                            <p class="mb-0 text-secondary-custom"><?= htmlspecialchars($cotizacion['nombre']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-admin">Correo Electrónico</label>
                            <p class="mb-0"><a href="mailto:<?= htmlspecialchars($cotizacion['correo']) ?>" style="color: var(--info); text-decoration: none;"><?= htmlspecialchars($cotizacion['correo']) ?></a></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-admin">Teléfono</label>
                            <p class="mb-0 text-secondary-custom"><?= htmlspecialchars($cotizacion['telefono'] ?: 'No proporcionado') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="bi bi-layers me-2"></i>Servicios Solicitados</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (empty($servicios)): ?>
                        <p class="text-muted-custom">No se especificaron servicios concretos o la lista está vacía.</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($servicios as $srv): ?>
                                <span class="service-tag">
                                    <i class="bi bi-check2-square"></i> <?= htmlspecialchars($srv) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna 2: Totales y Estado -->
        <div class="col-lg-4">
            <div class="admin-card mb-4" style="background: linear-gradient(135deg, rgba(108, 99, 255, 0.05), rgba(108, 99, 255, 0.15)); border-color: rgba(108, 99, 255, 0.3);">
                <div class="admin-card-body text-center py-5">
                    <label class="form-label-admin text-center mb-3">Presupuesto Estimado</label>
                    <h2 style="font-size: 2.5rem; font-weight: 900; color: var(--success); margin: 0;">
                        $<?= number_format($cotizacion['presupuesto_estimado'], 2) ?>
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">USD Aprox.</p>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title"><i class="bi bi-activity me-2"></i>Gestión</h2>
                </div>
                <div class="admin-card-body">
                    <div class="mb-4">
                        <label class="form-label-admin">Fecha de Solicitud</label>
                        <p class="text-secondary-custom mb-0">
                            <i class="bi bi-calendar3 me-2"></i><?= date('d M Y - H:i', strtotime($cotizacion['fecha'])) ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="form-label-admin">Estado Actual</label>
                        <select class="form-select-admin estado-select" data-id="<?= $cotizacion['id_cotizacion'] ?>">
                            <?php foreach (['Pendiente', 'Revisada', 'Contactado', 'Descartada'] as $est): ?>
                            <option value="<?= $est ?>" <?= $cotizacion['estado'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text-admin d-block mt-2 text-muted-custom">Cambiar el estado enviará una notificación interna.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
</div>

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
                    <span style="font-size: 13px; color: #6C63FF; font-weight: 800;" id="pdfRef"><?= date('Y-m-d-Hi', strtotime($cotizacion['fecha'])) ?></span>
                </div>
            </div>
        </div>

        <div style="padding: 40px 60px;">
            <!-- Client & Meta Info -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 50px;">
                <!-- Client Info -->
                <div style="width: 55%;">
                    <h4 style="margin: 0 0 12px; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; border-bottom: 2px solid #6C63FF; padding-bottom: 6px; display: inline-block;">Preparado Para</h4>
                    <h2 style="margin: 0 0 5px; font-size: 20px; color: #0D0D1A; font-weight: 800;"><?= htmlspecialchars($cotizacion['empresa']) ?></h2>
                    <p style="margin: 0 0 3px; font-size: 13px; color: #333; font-weight: 600;">Atn: <?= htmlspecialchars($cotizacion['nombre']) ?></p>
                    <p style="margin: 0; font-size: 13px; color: #666;"><?= htmlspecialchars($cotizacion['correo']) ?></p>
                </div>
                
                <!-- Meta Data -->
                <div style="width: 40%;">
                    <h4 style="margin: 0 0 12px; font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; border-bottom: 2px solid #E5E5E5; padding-bottom: 6px; display: inline-block;">Detalles del Documento</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 4px 0; font-size: 12px; color: #6B6B85; font-weight: 600;">Fecha Emisión:</td>
                            <td style="padding: 4px 0; font-size: 12px; color: #1A1A2E; text-align: right; font-weight: 700;"><?= date('d M Y', strtotime($cotizacion['fecha'])) ?></td>
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
                    <tbody>
                        <?php foreach ($servicios as $index => $srv): ?>
                        <tr style="background: <?= $index % 2 === 0 ? '#FFFFFF' : '#FAFAFC' ?>; border-bottom: 1px solid #E5E5E5;">
                            <td style="padding: 12px 20px; font-size: 13px; color: #6B6B85; font-weight: 700; width: 5%;"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 12px 20px; font-size: 13px; color: #1A1A2E; font-weight: 600;"><?= htmlspecialchars($srv) ?></td>
                            <td style="padding: 12px 20px; font-size: 12px; color: #00D4AA; font-weight: 700; text-align: center; width: 15%;"><span style="background: rgba(0,212,170,0.1); padding: 4px 8px; border-radius: 4px;">INCLUIDO</span></td>
                        </tr>
                        <?php endforeach; ?>
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
                        <div style="font-size: 28px; color: #6C63FF; font-weight: 900; letter-spacing: -1px;">$<?= number_format($cotizacion['presupuesto_estimado'], 2) ?> USD</div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
<script>
document.querySelector('.estado-select').addEventListener('change', async function() {
    const id = this.getAttribute('data-id');
    const estado = this.value;
    try {
        const res = await fetch('/Proyecto_Servicios/admin/cotizaciones/cambiar-estado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id, estado})
        });
        const data = await res.json();
        if(data.success) {
            showToast('Estado de la cotización actualizado', 'success');
        } else {
            showToast('Error: ' + data.error, 'danger');
        }
    } catch(e) {
        showToast('Error de conexión', 'danger');
    }
});

document.getElementById('btnDescargarPdf')?.addEventListener('click', async function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';

    const element = document.getElementById('pdfTemplate');
    element.classList.remove('d-none');
    
    const opt = {
      margin:       0,
      filename:     `Propuesta_Devioz_<?= preg_replace('/\s+/', '_', $cotizacion['empresa']) ?>.pdf`,
      image:        { type: 'jpeg', quality: 1 },
      html2canvas:  { scale: 2, useCORS: true, logging: false },
      jsPDF:        { unit: 'px', format: [800, 1123], orientation: 'portrait' }
    };

    try {
        await html2pdf().set(opt).from(element).save();
    } catch(err) {
        console.error(err);
        showToast('Hubo un error al generar el PDF.', 'danger');
    } finally {
        element.classList.add('d-none');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
</body>
</html>
