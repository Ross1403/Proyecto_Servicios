<?php
// ============================================================
// admin/logros/editar.php
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/auth-check.php';
require_once __DIR__ . '/../../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /Proyecto_Servicios/admin/logros/listar.php');
    exit;
}

$activePage = 'logros';
$errors     = [];
$db         = getDB();

$stmtL = $db->prepare("SELECT * FROM logros WHERE id_logro = :id");
$stmtL->execute([':id' => $id]);
$logro = $stmtL->fetch();

if (!$logro) {
    header('Location: /Proyecto_Servicios/admin/logros/listar.php');
    exit;
}

$proyectos = $db->query("
    SELECT p.id_proyecto, p.nombre, c.nombre AS cliente_nombre 
    FROM proyectos p 
    INNER JOIN clientes c ON p.id_cliente = c.id_cliente
    ORDER BY c.nombre, p.nombre
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proyecto        = filter_input(INPUT_POST, 'id_proyecto', FILTER_VALIDATE_INT);
    $titulo             = trim($_POST['titulo'] ?? '');
    $descripcion        = trim($_POST['descripcion'] ?? '');
    $indicador_anterior = filter_input(INPUT_POST, 'indicador_anterior', FILTER_VALIDATE_FLOAT);
    $indicador_actual   = filter_input(INPUT_POST, 'indicador_actual', FILTER_VALIDATE_FLOAT);
    
    $porcentaje_mejora = 0;
    if ($indicador_anterior !== false && $indicador_actual !== false && $indicador_anterior != 0) {
        $porcentaje_mejora = (($indicador_actual - $indicador_anterior) / abs($indicador_anterior)) * 100;
    } elseif (isset($_POST['porcentaje_mejora']) && $_POST['porcentaje_mejora'] !== '') {
        $porcentaje_mejora = filter_input(INPUT_POST, 'porcentaje_mejora', FILTER_VALIDATE_FLOAT);
    }

    if (!$id_proyecto) $errors[] = 'Debes seleccionar un proyecto.';
    if (empty($titulo)) $errors[] = 'El título del logro es obligatorio.';

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE logros SET 
                    id_proyecto = :id_proyecto, 
                    titulo = :titulo, 
                    descripcion = :descripcion, 
                    indicador_anterior = :indicador_anterior, 
                    indicador_actual = :indicador_actual, 
                    porcentaje_mejora = :porcentaje_mejora
                WHERE id_logro = :id
            ");
            $stmt->execute([
                ':id_proyecto'        => $id_proyecto,
                ':titulo'             => $titulo,
                ':descripcion'        => $descripcion ?: null,
                ':indicador_anterior' => $indicador_anterior !== false ? $indicador_anterior : null,
                ':indicador_actual'   => $indicador_actual !== false ? $indicador_actual : null,
                ':porcentaje_mejora'  => $porcentaje_mejora,
                ':id'                 => $id
            ]);

            $_SESSION['flash'] = ['tipo' => 'success', 'msg' => 'Logro actualizado correctamente.'];
            header('Location: /Proyecto_Servicios/admin/logros/listar.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error de BD: ' . $e->getMessage();
        }
    }
    
    $logro = array_merge($logro, $_POST);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Logro | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>
<main class="admin-main">
<div class="admin-topbar">
    <button class="topbar-toggle" id="sidebarOpen"><i class="bi bi-list"></i></button>
    <span class="topbar-title">Editar Logro</span>
</div>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <ul class="breadcrumb-admin">
                <li><a href="/Proyecto_Servicios/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/Proyecto_Servicios/admin/logros/listar.php">Logros</a></li>
                <li>Editar</li>
            </ul>
            <h1 class="admin-page-title">Editar Logro</h1>
        </div>
        <a href="/Proyecto_Servicios/admin/logros/listar.php" class="btn-admin-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert-admin alert-admin-danger mb-4">
        <div>
            <i class="bi bi-exclamation-circle-fill"></i>
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-body">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Proyecto Asociado *</label>
                            <select name="id_proyecto" class="form-select-admin" required>
                                <?php foreach ($proyectos as $p): ?>
                                <option value="<?= $p['id_proyecto'] ?>" <?= $logro['id_proyecto'] == $p['id_proyecto'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['cliente_nombre']) ?> — <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Título / Métrica principal *</label>
                            <input type="text" name="titulo" class="form-control-admin" required value="<?= htmlspecialchars($logro['titulo']) ?>">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Descripción</label>
                            <textarea name="descripcion" class="form-control-admin" rows="3"><?= htmlspecialchars($logro['descripcion']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Indicador Anterior</label>
                            <input type="number" step="0.01" id="ind_ant" name="indicador_anterior" class="form-control-admin" value="<?= htmlspecialchars($logro['indicador_anterior']) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Indicador Actual</label>
                            <input type="number" step="0.01" id="ind_act" name="indicador_actual" class="form-control-admin" value="<?= htmlspecialchars($logro['indicador_actual']) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Mejora (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="pct_mejora" name="porcentaje_mejora" class="form-control-admin" style="border-top-right-radius:0; border-bottom-right-radius:0;" value="<?= htmlspecialchars($logro['porcentaje_mejora']) ?>">
                                <span class="input-group-text" style="background:var(--admin-input); border:1px solid var(--admin-border); color:var(--text-muted);">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3" style="border-top:1px solid var(--admin-border);">
                    <button type="submit" class="btn-admin-primary">
                        <i class="bi bi-save-fill"></i> Actualizar logro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Proyecto_Servicios/assets/js/admin.js"></script>
<script>
const ant = document.getElementById('ind_ant');
const act = document.getElementById('ind_act');
const pct = document.getElementById('pct_mejora');

function calcPct() {
    const vAnt = parseFloat(ant.value);
    const vAct = parseFloat(act.value);
    if (!isNaN(vAnt) && !isNaN(vAct) && vAnt !== 0) {
        let diff = ((vAct - vAnt) / Math.abs(vAnt)) * 100;
        pct.value = diff.toFixed(2);
    }
}
ant.addEventListener('input', calcPct);
act.addEventListener('input', calcPct);
</script>
</body>
</html>
