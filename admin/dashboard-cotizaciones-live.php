<?php
// admin/dashboard-cotizaciones-live.php — Endpoint en tiempo real para Dashboard
session_start();
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();

    $totalCotizaciones      = (int)$db->query("SELECT COUNT(*) FROM cotizaciones")->fetchColumn();
    $cotizacionesPendientes = (int)$db->query("SELECT COUNT(*) FROM cotizaciones WHERE estado = 'Pendiente'")->fetchColumn();
    $volumenEstimado        = (float)$db->query("SELECT SUM(presupuesto_estimado) FROM cotizaciones WHERE estado != 'Descartada'")->fetchColumn() ?: 0.0;
    
    $rows = $db->query("SELECT id_cotizacion, nombre, empresa, presupuesto_estimado, estado, fecha FROM cotizaciones ORDER BY id_cotizacion DESC LIMIT 5")->fetchAll();

    $cotizaciones = [];
    foreach ($rows as $r) {
        $ts = strtotime($r['fecha']);
        $cotizaciones[] = [
            'id'          => $r['id_cotizacion'],
            'nombre'      => htmlspecialchars($r['nombre']),
            'empresa'     => !empty($r['empresa']) ? htmlspecialchars($r['empresa']) : null,
            'presupuesto' => $r['presupuesto_estimado'] ? 'S/ ' . number_format($r['presupuesto_estimado'], 2) : 'A definir',
            'estado'      => $r['estado'],
            'fecha_dia'   => date('d/m/Y', $ts),
            'fecha_hora'  => date('h:i A', $ts)
        ];
    }

    echo json_encode([
        'success' => true,
        'stats'   => [
            'total'      => $totalCotizaciones,
            'pendientes' => $cotizacionesPendientes,
            'volumen'    => number_format($volumenEstimado, 2)
        ],
        'cotizaciones' => $cotizaciones
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
