<?php
// ============================================================
// clientes.php — Listado de clientes con filtro y búsqueda real
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Clientes';
$pageDesc  = 'Empresas líderes que confían en Devioz Proyectos para sus proyectos de transformación digital.';

$db = getDB();

// ---- Sanitizar y leer filtros ----
$search = trim($_GET['q']      ?? '');
$sector = trim($_GET['sector'] ?? '');

// ---- Sectores únicos para el select ----
$sectores = $db->query("SELECT DISTINCT sector FROM clientes WHERE estado = 1 AND sector IS NOT NULL ORDER BY sector")
               ->fetchAll(PDO::FETCH_COLUMN);

// ---- Query con filtros ----
$sql    = "SELECT * FROM clientes WHERE estado = 1";
$params = [];

if ($search !== '') {
    $sql     .= " AND nombre LIKE :q";
    $params[':q'] = '%' . $search . '%';
}

if ($sector !== '') {
    $sql     .= " AND sector = :sector";
    $params[':sector'] = $sector;
}

$sql .= " ORDER BY destacado DESC, nombre ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="detail-hero">
    <div class="container text-center">
        <span class="section-badge"><i class="bi bi-building"></i> Clientes</span>
        <h1 class="section-title mt-2">Empresas que confían en Devioz</h1>
        <p class="section-subtitle mx-auto mt-3">Líderes de industria en Perú que transformaron sus negocios con nuestra tecnología.</p>
    </div>
</section>

<section class="bg-dark-2">
    <div class="container">

        <!-- Barra de filtros -->
        <div class="filter-bar mb-4">
            <form method="GET" action="clientes.php" class="row g-3 align-items-end" id="filterForm">
                <div class="col-md-6">
                    <label for="searchCliente" class="form-label" style="font-size:.8rem;color:var(--text-muted)">Buscar cliente</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:var(--dark-3);border-color:var(--glass-border)">
                            <i class="bi bi-search" style="color:var(--text-muted)"></i>
                        </span>
                        <input type="text"
                               id="searchCliente"
                               name="q"
                               class="input-dark"
                               placeholder="Buscar por nombre..."
                               value="<?= htmlspecialchars($search) ?>"
                               style="border-radius:0 10px 10px 0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="filterSector" class="form-label" style="font-size:.8rem;color:var(--text-muted)">Filtrar por sector</label>
                    <select id="filterSector" name="sector" class="input-dark" style="cursor:pointer">
                        <option value="">Todos los sectores</option>
                        <?php foreach ($sectores as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= ($sector === $s) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" id="btnFiltrar" class="btn-primary-custom w-100" style="justify-content:center">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if ($search || $sector): ?>
                    <a href="clientes.php" class="btn-outline-custom" style="padding:.75rem;justify-content:center" title="Limpiar filtros">
                        <i class="bi bi-x"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Resultado -->
        <?php if ($search || $sector): ?>
        <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem">
            <?= count($clientes) ?> resultado(s)
            <?= $search ? ' para "<strong>' . htmlspecialchars($search) . '</strong>"' : '' ?>
            <?= $sector ? ' en sector "<strong>' . htmlspecialchars($sector) . '</strong>"' : '' ?>
        </p>
        <?php endif; ?>

        <?php if (empty($clientes)): ?>
        <div class="text-center py-5" style="color:var(--text-muted)">
            <i class="bi bi-building-slash" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3">No se encontraron clientes con esos criterios de búsqueda.</p>
            <a href="clientes.php" class="btn-outline-custom mt-2" style="font-size:.85rem;padding:.5rem 1.2rem">Limpiar filtros</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($clientes as $c): ?>
            <div class="col-sm-6 col-md-4 col-lg-3 animate-on-scroll">
                <a href="cliente.php?id=<?= $c['id_cliente'] ?>" class="d-block text-decoration-none h-100">
                    <div class="client-card position-relative h-100">
                        <?php if ($c['destacado']): ?>
                        <span style="position:absolute;top:.75rem;right:.75rem;font-size:.65rem;padding:.2rem .5rem"
                              class="badge-estado badge-finalizado">
                            <i class="bi bi-star-fill"></i> Destacado
                        </span>
                        <?php endif; ?>

                        <?php if ($c['logo'] && file_exists('uploads/clientes/' . $c['logo'])): ?>
                            <img src="uploads/clientes/<?= htmlspecialchars($c['logo']) ?>"
                                 alt="<?= htmlspecialchars($c['nombre']) ?>"
                                 style="max-height:60px;max-width:120px;object-fit:contain;margin-bottom:1rem">
                        <?php else: ?>
                            <div class="client-logo"><?= strtoupper(mb_substr($c['nombre'], 0, 2)) ?></div>
                        <?php endif; ?>

                        <p class="fw-bold mb-0" style="font-size:.95rem;color:var(--text-primary)"><?= htmlspecialchars($c['nombre']) ?></p>
                        <p style="font-size:.78rem;color:var(--text-muted);margin:.3rem 0"><?= htmlspecialchars($c['sector']) ?></p>
                        <p style="font-size:.82rem;color:var(--text-secondary);line-height:1.5">
                            <?= htmlspecialchars(mb_strimwidth($c['descripcion'], 0, 80, '…')) ?>
                        </p>
                        <span class="btn-outline-custom mt-2" style="font-size:.78rem;padding:.3rem .8rem;display:inline-flex">
                            Ver perfil <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
