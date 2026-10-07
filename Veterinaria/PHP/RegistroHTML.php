<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "Conexion.php";
require_once "Login.php";
require_once "Usuario.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ci          = trim($_POST['ci']);
    $nombre      = trim($_POST['nombre']);
    $apellido    = trim($_POST['apellido']);
    $correo      = trim($_POST['correo']);
    $telefono    = trim($_POST['telefono']);
    $calle1      = trim($_POST['calle1']);
    $calle2      = trim($_POST['calle2']);
    $direccion   = $calle1 . ", " . $calle2;
    $contraseña  = $_POST['password'];
    $confirmar   = $_POST['confirm_password'];
    $tipoUsuario = $_POST['tipo_usuario'];

    if ($contraseña !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $usuario = new Usuario();
        $resultado = $usuario->registrar($ci, $nombre, $apellido, $correo, $telefono, $direccion, $contraseña, $tipoUsuario);

        if ($resultado) {
            $_SESSION["correo"] = $correo;
            header("Location: LoginHTML.php");
            exit();
        } else {
            $error = "No se pudo completar el registro. Verifica si el correo o la C.I. ya existen.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - Clínica Veterinaria Sienra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .auth-form-wide {
            width: 100%;
            max-width: 560px;
        }
    </style>
</head>

<body class="auth-body">

    <header class="site-header site-header-dark">
        <nav class="navbar">
            <div class="container-fluid px-3 px-lg-4 justify-content-between">
                <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                    <img src="img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
                    <span class="brand-text brand-text-light">Clínica<br>Veterinaria<br>SIENRA</span>
                </a>

                <a class="nav-link icon-badge icon-badge-user" href="LoginHTML.php" aria-label="Cuenta">
                    <i class="bi bi-person-fill"></i>
                </a>
            </div>
        </nav>
    </header>

    <main class="auth-main d-flex align-items-center justify-content-center py-3">
        <form class="auth-form auth-form-wide" action="RegistroHTML.php" method="post">
            <h1 class="auth-title mb-3 text-center">Crear cuenta</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="row g-2">

                <div class="col-6 mb-2">
                    <label for="ci" class="form-label">C.I.</label>
                    <input type="text" class="form-control auth-input" id="ci" name="ci" placeholder="1.234.567-8" required>
                </div>
                <div class="col-6 mb-2">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control auth-input" id="telefono" name="telefono" placeholder="097 654 321" required>
                </div>

                <div class="col-6 mb-2">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control auth-input" id="nombre" name="nombre" placeholder="Jorge" required>
                </div>
                <div class="col-6 mb-2">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control auth-input" id="apellido" name="apellido" placeholder="Pérez" required>
                </div>

                <div class="col-12 mb-2">
                    <label for="Tipo de usuario" class="form-label">Tipo de usuario</label>
                    <select class="form-control auth-input" id="tipo_usuario" name="tipo_usuario" required>
                        <option value="">Seleccionar</option>
                        <option value="cliente">Cliente</option>
                        <option value="empleado">Empleado</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="col-6 mb-2">
                    <label for="calle1" class="form-label">Calle 1</label>
                    <input type="text" class="form-control auth-input" id="calle1" name="calle1" placeholder="18 de Julio" required>
                </div>
                <div class="col-6 mb-2">
                    <label for="calle2" class="form-label">Calle 2</label>
                    <input type="text" class="form-control auth-input" id="calle2" name="calle2" placeholder="Arturo Santana">
                </div>

                <div class="col-12 mb-2">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control auth-input" id="correo" name="correo" placeholder="jorgeperez@gmail.com" required>
                </div>

                <div class="col-6 mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control auth-input" id="password" name="password" placeholder="*******" required>
                </div>
                <div class="col-6 mb-3">
                    <label for="confirm_password" class="form-label">Repetir contraseña</label>
                    <input type="password" class="form-control auth-input" id="confirm_password" name="confirm_password" placeholder="*******" required>
                </div>
            </div>

            <button type="submit" name="boton" class="btn btn-cta auth-submit mt-2">Crear cuenta</button>

            <p class="auth-footer-text mt-3 mb-0">
                ¿Ya tenés cuenta? <a href="LoginHTML.php" class="auth-link">Iniciá sesión</a>
            </p>
        </form>
    </main>

</body>

</html>