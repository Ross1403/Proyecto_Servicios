<?php
// ============================================================
// admin/cotizaciones/imprimir.php — Generación de PDF (Senior Pro)
// Utiliza @media print para generar un documento listo para PDF
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID inválido");
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM cotizaciones WHERE id_cotizacion = :id");
$stmt->execute([':id' => $id]);
$cotizacion = $stmt->fetch();

if (!$cotizacion) {
    die("Cotización no encontrada");
}

$servicios = json_decode($cotizacion['servicios_json'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización #<?= str_pad($cotizacion['id_cotizacion'], 4, '0', STR_PAD_LEFT) ?> - Devioz Proyectos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #fff; color: #000; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #00D4AA; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #38BDF8; }
        .details { margin-bottom: 30px; }
        .table-pdf th { background: #f8f9fa !important; color: #333 !important; }
        .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; color: #00D4AA; }
        
        /* Ocultar botón al imprimir (Generar PDF) */
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none; box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="text-center mb-4 no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Guardar como PDF / Imprimir
        </button>
        <a href="/Proyecto_Servicios/admin/cotizaciones/listar.php" class="btn btn-secondary">Volver</a>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">Devioz Proyectos</div>
            <div class="text-end">
                <h4 class="mb-0">COTIZACIÓN</h4>
                <p class="text-muted mb-0">#<?= str_pad($cotizacion['id_cotizacion'], 4, '0', STR_PAD_LEFT) ?></p>
                <small>Fecha: <?= date('d/m/Y', strtotime($cotizacion['fecha'])) ?></small>
            </div>
        </div>

        <div class="row details">
            <div class="col-6">
                <h6 class="text-muted">Preparado para:</h6>
                <strong><?= htmlspecialchars($cotizacion['nombre']) ?></strong><br>
                <?= htmlspecialchars($cotizacion['empresa'] ?: 'Independiente') ?><br>
                <?= htmlspecialchars($cotizacion['correo']) ?><br>
                <?= htmlspecialchars($cotizacion['telefono']) ?>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-muted">Emitido por:</h6>
                <strong>Devioz Consultora TI</strong><br>
                contacto@devioz.com<br>
                +51 987 654 321
            </div>
        </div>

        <table class="table table-bordered table-pdf">
            <thead>
                <tr>
                    <th>Servicio Seleccionado</th>
                    <th class="text-end">Costo Estimado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicios as $srv): ?>
                <tr>
                    <td><?= htmlspecialchars($srv['nombre'] ?? $srv) ?></td>
                    <td class="text-end">Incluido</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            Total Estimado: S/ <?= number_format($cotizacion['presupuesto_estimado'], 2) ?>
        </div>
        
        <p class="text-muted mt-5" style="font-size: 0.85rem; text-align: center;">
            Este documento es un presupuesto estimado y está sujeto a validación técnica final. Validez de 15 días.
        </p>
    </div>
</body>
</html>
