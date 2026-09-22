<?php
// ============================================================
// contacto.php — Formulario de contacto con INSERT real a BD
// Soporta petición AJAX (X-Requested-With) y POST normal
// ============================================================
require_once 'config/database.php';

$pageTitle = 'Contacto';
$pageDesc  = 'Contáctanos para hablar sobre tu proyecto tecnológico. Devioz Proyectos — Lima, Perú.';

$db = getDB();

// ---- Servicios para el select del formulario ----
$servicios = $db->query("SELECT nombre FROM servicios WHERE estado = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_COLUMN);

// ---- Pre-rellenar servicio desde URL ----
$servicioPreselect = trim($_GET['servicio'] ?? '');

// ============================================================
// PROCESAMIENTO DEL FORMULARIO (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    // ---- Recoger y sanitizar datos ----
    $nombre   = trim(filter_input(INPUT_POST, 'nombre',   FILTER_SANITIZE_SPECIAL_CHARS));
    $empresa  = trim(filter_input(INPUT_POST, 'empresa',  FILTER_SANITIZE_SPECIAL_CHARS));
    $correo   = trim(filter_input(INPUT_POST, 'correo',   FILTER_SANITIZE_EMAIL));
    $telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_SPECIAL_CHARS));
    $servicio = trim(filter_input(INPUT_POST, 'servicio', FILTER_SANITIZE_SPECIAL_CHARS));
    $mensaje  = trim(filter_input(INPUT_POST, 'mensaje',  FILTER_SANITIZE_SPECIAL_CHARS));

    // ---- Validaciones server-side ----
    $errors = [];

    if (empty($nombre) || mb_strlen($nombre) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres.';
    }

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un correo electrónico válido.';
    }

    if (!empty($telefono) && !preg_match('/^\+?[\d\s\-\(\)]{7,15}$/', $telefono)) {
        $errors[] = 'El teléfono ingresado no es válido.';
    }

    if (empty($mensaje) || mb_strlen($mensaje) < 20) {
        $errors[] = 'El mensaje debe tener al menos 20 caracteres.';
    }

    // ---- Insertar en BD si no hay errores ----
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO contactos (nombre, empresa, correo, telefono, servicio, mensaje, estado)
                VALUES (:nombre, :empresa, :correo, :telefono, :servicio, :mensaje, 'Nuevo')
            ");
            $stmt->execute([
                ':nombre'   => $nombre,
                ':empresa'  => $empresa ?: null,
                ':correo'   => $correo,
                ':telefono' => $telefono ?: null,
                ':servicio' => $servicio ?: null,
                ':mensaje'  => $mensaje,
            ]);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'mensaje' => '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.']);
                exit;
            }

            $successMsg = '¡Gracias! Tu mensaje fue enviado correctamente. Te contactaremos a la brevedad.';
        } catch (PDOException $e) {
            error_log('Error insertar contacto: ' . $e->getMessage());
            $errorMsg = 'Error al guardar el mensaje. Por favor inténtalo nuevamente.';

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'mensaje' => $errorMsg]);
                exit;
            }
        }
    } else {
        $errorMsg = implode(' ', $errors);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'mensaje' => $errorMsg]);
            exit;
        }
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<section class="detail-hero">
    <div class="container text-center">
        <span class="section-badge"><i class="bi bi-chat-dots-fill"></i> Contacto</span>
        <h1 class="section-title mt-2">Hablemos de tu proyecto</h1>
        <p class="section-subtitle mx-auto mt-3">
            Cuéntanos qué necesitas y un consultor de Devioz te responderá en menos de 24 horas.
        </p>
    </div>
</section>

<section class="form-section">
    <div class="container">
        <div class="row gy-5 justify-content-center">

            <!-- Formulario -->
            <div class="col-lg-7">
                <?php if (!empty($successMsg)): ?>
                <div class="alert-custom-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMsg) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($errorMsg)): ?>
                <div class="alert-custom-danger mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($errorMsg) ?>
                </div>
                <?php endif; ?>

                <div class="card-glass">
                    <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:1.5rem">Envíanos un mensaje</h2>

                    <form id="contactForm" method="POST" action="contacto.php" class="form-floating-dark" novalidate>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Nombre completo *</label>
                                <input type="text"
                                       id="nombre" name="nombre"
                                       class="input-dark"
                                       placeholder="Tu nombre"
                                       required minlength="2"
                                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Empresa</label>
                                <input type="text"
                                       id="empresa" name="empresa"
                                       class="input-dark"
                                       placeholder="Nombre de tu empresa"
                                       value="<?= htmlspecialchars($_POST['empresa'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="correo" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Correo electrónico *</label>
                                <input type="email"
                                       id="correo" name="correo"
                                       class="input-dark"
                                       placeholder="tu@correo.com"
                                       required
                                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Teléfono</label>
                                <input type="tel"
                                       id="telefono" name="telefono"
                                       class="input-dark"
                                       placeholder="+51 999 999 999"
                                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                <div class="form-error"></div>
                            </div>
                            <div class="col-12">
                                <label for="servicio" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Servicio de interés</label>
                                <select id="servicio" name="servicio" class="input-dark" style="cursor:pointer">
                                    <option value="">Selecciona un servicio...</option>
                                    <?php foreach ($servicios as $srv): ?>
                                    <option value="<?= htmlspecialchars($srv) ?>"
                                            <?= (($servicioPreselect === $srv) || (isset($_POST['servicio']) && $_POST['servicio'] === $srv)) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($srv) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="mensaje" class="form-label" style="font-size:.82rem;color:var(--text-muted)">Mensaje *</label>
                                <textarea id="mensaje" name="mensaje"
                                          class="input-dark" rows="5"
                                          placeholder="Cuéntanos sobre tu proyecto o necesidad (mínimo 20 caracteres)..."
                                          required minlength="20"><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                                <div class="form-error"></div>
                            </div>
                        </div>

                        <div id="formFeedback" class="mt-3"></div>

                        <button type="submit" id="contactSubmitBtn" class="btn-primary-custom mt-4 w-100" style="justify-content:center">
                            <i class="bi bi-send-fill me-2"></i>Enviar mensaje
                        </button>
                        <p style="font-size:.75rem;color:var(--text-muted);text-align:center;margin-top:.75rem">
                            Tu información es confidencial y nunca será compartida con terceros.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Info de contacto -->
            <div class="col-lg-4">
                <div class="card-glass mb-4">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem">Información de contacto</h3>
                    <div style="display:flex;flex-direction:column;gap:1.25rem">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="service-icon mb-0" style="width:40px;height:40px;font-size:1rem;flex-shrink:0">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0">Correo</p>
                                <a href="mailto:contacto@devioz.com" class="text-link fw-bold" style="font-size:.9rem">contacto@devioz.com</a>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="service-icon mb-0" style="width:40px;height:40px;font-size:1rem;flex-shrink:0">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0">Teléfono</p>
                                <a href="tel:+51014567890" class="text-link fw-bold" style="font-size:.9rem">+51 (01) 456-7890</a>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="service-icon mb-0" style="width:40px;height:40px;font-size:1rem;flex-shrink:0">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0">Oficina</p>
                                <p class="fw-bold mb-0" style="font-size:.9rem">San Isidro, Lima, Perú</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="service-icon mb-0" style="width:40px;height:40px;font-size:1rem;flex-shrink:0">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <p style="font-size:.78rem;color:var(--text-muted);margin:0">Horario</p>
                                <p class="fw-bold mb-0" style="font-size:.9rem">Lun – Vie: 9am – 6pm</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-glass">
                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem">¿Por qué Devioz?</h3>
                    <?php
                    $puntos = [
                        ['bi-lightning-charge-fill', 'Respuesta en menos de 24 horas'],
                        ['bi-shield-check-fill', 'Consulta inicial sin costo'],
                        ['bi-graph-up-arrow', 'Propuestas con KPIs definidos'],
                        ['bi-headset', 'Soporte post-entrega garantizado'],
                    ];
                    foreach ($puntos as $p): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi <?= $p[0] ?>" style="color:var(--success);font-size:1rem;flex-shrink:0"></i>
                        <span style="font-size:.85rem;color:var(--text-secondary)"><?= $p[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
