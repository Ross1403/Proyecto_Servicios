<?php
// admin/cotizaciones/listar.php
$activePage = 'cotizaciones';
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM cotizaciones ORDER BY fecha DESC");
    $cotizaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error de base de datos.");
}
?>

<div class="admin-layout d-flex">
    <?php require_once __DIR__ . '/../../includes/admin-sidebar.php'; ?>

    <main class="admin-main flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Cotizaciones Generadas</h2>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['tipo'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente / Empresa</th>
                                <th>Contacto</th>
                                <th>Presupuesto Est.</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cotizaciones)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No hay cotizaciones registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cotizaciones as $c): ?>
                                    <tr>
                                        <td>#<?= str_pad($c['id_cotizacion'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($c['empresa']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($c['nombre']) ?></small>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($c['correo']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($c['correo']) ?>
                                            </a>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($c['telefono']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success fs-6">
                                                $<?= number_format($c['presupuesto_estimado'], 2) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
                                        <td>
                                            <select class="form-select form-select-sm status-select" 
                                                    style="width: 130px;"
                                                    data-id="<?= $c['id_cotizacion'] ?>" 
                                                    data-table="cotizaciones"
                                                    data-field="estado"
                                                    data-url="/Proyecto_Servicios/admin/cotizaciones/cambiar-estado.php">
                                                <?php foreach (['Pendiente', 'Revisada', 'Contactado', 'Descartada'] as $est): ?>
                                                    <option value="<?= $est ?>" <?= $c['estado'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
