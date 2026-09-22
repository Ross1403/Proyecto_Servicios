<?php
// ============================================================
// admin/login.php — Login del panel administrativo
// ============================================================
session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: /Proyecto_Servicios/admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$error = '';

// ============================================================
// PROCESAMIENTO DEL LOGIN
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim(filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL));
    $pass   = $_POST['password'] ?? '';

    if (empty($correo) || empty($pass)) {
        $error = 'Ingresa tu correo y contraseña.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo ingresado no es válido.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = :correo AND estado = 1 LIMIT 1");
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($pass, $usuario['password'])) {
            // ---- Crear sesión segura ----
            session_regenerate_id(true);
            $_SESSION['admin_id']     = $usuario['id_usuario'];
            $_SESSION['admin_nombre'] = $usuario['nombre'];
            $_SESSION['admin_correo'] = $usuario['correo'];
            $_SESSION['admin_rol']    = $usuario['rol'];
            $_SESSION['last_regen']   = time();

            // Redirigir a URL solicitada o dashboard
            $redirect = $_SESSION['redirect_after_login'] ?? '/Proyecto_Servicios/admin/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            // Simular delay para mitigar brute-force
            sleep(1);
            $error = 'Correo o contraseña incorrectos. Verifica tus datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar sesión | Devioz Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Proyecto_Servicios/assets/css/admin.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: radial-gradient(ellipse 80% 60% at 30% 30%, rgba(108,99,255,.15) 0%, transparent 60%), var(--admin-bg); }
        .login-card { width: 100%; max-width: 420px; padding: 2rem; }
        .login-logo { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-bottom: 2rem; font-size: 1.6rem; font-weight: 900; }
        .login-logo b { color: var(--accent); font-weight: inherit; }
        .login-logo i { color: var(--primary); }
        .login-title { text-align: center; margin-bottom: 2rem; }
        .login-title h1 { font-size: 1.3rem; font-weight: 700; color: var(--text-primary); margin-bottom: .3rem; }
        .login-title p { font-size: .85rem; color: var(--text-muted); }
        .pass-wrapper { position: relative; }
        .btn-eye { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; padding: 0; transition: color .2s; }
        .btn-eye:hover { color: var(--primary-light); }
    </style>
</head>
<body>
<div class="login-card admin-card">
    <div class="login-logo">
        <i class="bi bi-layers-fill"></i>
        Devioz<b>.</b>Admin
    </div>
    <div class="login-title">
        <h1>Bienvenido de vuelta</h1>
        <p>Ingresa tus credenciales para continuar</p>
    </div>

    <?php if ($error): ?>
    <div class="alert-admin alert-admin-danger mb-4">
        <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/Proyecto_Servicios/admin/login.php" id="loginForm">
        <div class="form-group-admin">
            <label for="correo" class="form-label-admin">Correo electrónico</label>
            <input type="email"
                   id="correo" name="correo"
                   class="form-control-admin"
                   placeholder="admin@devioz.com"
                   required
                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                   autocomplete="email">
        </div>

        <div class="form-group-admin">
            <label for="password" class="form-label-admin">Contraseña</label>
            <div class="pass-wrapper">
                <input type="password"
                       id="password" name="password"
                       class="form-control-admin"
                       placeholder="••••••••"
                       required
                       autocomplete="current-password"
                       style="padding-right:2.5rem">
                <button type="button" class="btn-eye" id="togglePass" aria-label="Mostrar contraseña">
                    <i class="bi bi-eye-slash" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-admin-primary w-100 mt-2" style="justify-content:center;padding:.75rem">
            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="/Proyecto_Servicios/index.php" style="font-size:.8rem;color:var(--text-muted);text-decoration:none">
            <i class="bi bi-arrow-left me-1"></i>Volver al sitio público
        </a>
    </div>

    <div class="mt-3 p-3 rounded" style="background:rgba(108,99,255,.07);border:1px solid rgba(108,99,255,.15)">
        <p style="font-size:.75rem;color:var(--text-muted);margin:0;text-align:center">
            <i class="bi bi-info-circle me-1"></i>
            Demo: <strong style="color:var(--primary-light)">admin@devioz.com</strong> / <strong style="color:var(--primary-light)">password</strong>
        </p>
    </div>
</div>

<script>
    document.getElementById('togglePass').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
</script>
</body>
</html>
