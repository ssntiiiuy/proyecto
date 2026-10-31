<?php
session_start();

require_once __DIR__ . '/clases/Conexion.php';
require_once __DIR__ . '/clases/Login.php';

$error = "";

/* aviso que deja RecuperarHTML.php (se muestra una sola vez) */
$aviso = $_SESSION['aviso_codigo'] ?? "";
unset($_SESSION['aviso_codigo']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo     = trim($_POST['codigo'] ?? '');
    $contraseña = $_POST['password'] ?? '';
    $confirmar  = $_POST['confirm_password'] ?? '';

    $datos = $_SESSION['recuperar'] ?? null;

    if (!preg_match('/^[0-9]{6}$/', $codigo)) {
        $error = "El código tiene que tener 6 números.";
    } elseif ($contraseña === '') {
        $error = "Escribí la contraseña nueva.";
    } elseif ($contraseña !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif ($datos === null) {
        /* no hay código pedido (o el correo no estaba registrado) */
        $error = "El código es incorrecto o venció. Pedí uno nuevo.";
    } elseif (time() > $datos['vence']) {
        unset($_SESSION['recuperar']);
        $error = "El código venció (dura 15 minutos). Pedí uno nuevo.";
    } elseif (!hash_equals($datos['codigo'], $codigo)) {
        $_SESSION['recuperar']['intentos']++;

        if ($_SESSION['recuperar']['intentos'] >= 3) {
            unset($_SESSION['recuperar']);
            $error = "Código incorrecto. Se terminaron los 3 intentos, pedí un código nuevo.";
        } else {
            $quedan = 3 - $_SESSION['recuperar']['intentos'];
            $error = "Código incorrecto. Te quedan " . $quedan . " intento(s).";
        }
    } else {
        $login = new Login();
        if ($login->cambiarContrasena($datos['correo'], $contraseña)) {
            unset($_SESSION['recuperar']);
            $_SESSION['aviso'] = "Tu contraseña se cambió. Ya podés iniciar sesión.";
            header("Location: LoginHTML.php");
            exit();
        } else {
            $error = "No se pudo cambiar la contraseña. Intentá de nuevo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña - Clínica Veterinaria Sienra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../CSS/style.css">
    <script src="../JS/tema.js"></script>
</head>

<body class="auth-body">

    <header class="site-header site-header-dark">
        <nav class="navbar">
            <div class="container-fluid px-3 px-lg-4 justify-content-between">
                <div class="navbar-brand d-flex align-items-center gap-2">
                    <img src="../img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
                    <span class="brand-text brand-text-light">Clínica<br>Veterinaria<br>SIENRA</span>
                </div>

                <a class="nav-link icon-badge icon-badge-user" href="LoginHTML.php" aria-label="Cuenta">
                    <i class="bi bi-person-fill"></i>
                </a>
            </div>
        </nav>
    </header>

    <main class="auth-main d-flex align-items-center justify-content-center">
        <form class="auth-form" action="VerificarCodigoHTML.php" method="post">
            <h1 class="auth-title">Cambiar contraseña</h1>

            <?php if (!empty($aviso)) { ?>
                <div class="alert alert-success py-2 text-center" role="alert">
                    <?= htmlspecialchars($aviso) ?>
                </div>
            <?php } ?>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php } ?>

            <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" class="form-control auth-input" id="codigo" name="codigo" placeholder="123456" pattern="[0-9]{6}" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña nueva</label>
                <div class="input-group">
                    <input type="checkbox" id="togglePassword" class="d-none toggle-check">
                    <input type="text" class="form-control auth-input input-password" id="password" name="password" placeholder="******" required>
                    <label for="togglePassword" class="btn auth-eye-btn d-flex align-items-center">
                        <i class="bi bi-eye icon-eye"></i>
                        <i class="bi bi-eye-slash icon-eye-slash"></i>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Repetir contraseña nueva</label>
                <div class="input-group">
                    <input type="checkbox" id="toggleConfirmPassword" class="d-none toggle-check">
                    <input type="text" class="form-control auth-input input-password" id="confirm_password" name="confirm_password" placeholder="******" required>
                    <label for="toggleConfirmPassword" class="btn auth-eye-btn d-flex align-items-center">
                        <i class="bi bi-eye icon-eye"></i>
                        <i class="bi bi-eye-slash icon-eye-slash"></i>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-cta auth-submit">Cambiar contraseña</button>

            <p class="auth-footer-text">
                <a href="RecuperarHTML.php" class="auth-link">Pedir un código nuevo</a>
                ·
                <a href="LoginHTML.php" class="auth-link">Volver a iniciar sesión</a>
            </p>
        </form>
    </main>

</body>

</html>
