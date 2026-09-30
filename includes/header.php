<?php
// ============================================================
// includes/header.php — Cabecera HTML pública
// Variables esperadas: $pageTitle (string), $pageDesc (string)
// ============================================================
if (!isset($pageTitle)) $pageTitle = 'Devioz Proyectos';
if (!isset($pageDesc))  $pageDesc  = 'Transformamos necesidades empresariales en soluciones tecnológicas.';

// Calcular base path dinámicamente
$depth = substr_count(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
// Si estamos en la raíz del proyecto, base = ''
if ($depth <= 1) $base = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?> | Devioz Proyectos">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta property="og:type" content="website">
    <title><?= htmlspecialchars($pageTitle) ?> | Devioz Proyectos</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
