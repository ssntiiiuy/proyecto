<?php
require_once "Conexion.php";
require_once "Login.php";

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email']);
    $pass   = $_POST['password'];

    $login = new Login();
    if ($login->validar($correo, $pass)) {

        $conexion = new Conexion();

        $sql = "SELECT tipoUsuario FROM login WHERE Correo = :correo";
        $stmt = $conexion->establecerConexion()->prepare($sql);

        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario['tipoUsuario'] === 'admin') {
            header("Location: indexEncargado.php");
        } elseif ($usuario['tipoUsuario'] === 'veterinario') {
            header("Location: indexVeterinario.php");
        } else {
            header("Location: index.php");
        }
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - Clínica Veterinaria Sienra</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../CSS/style.css">
</head>

<body class="auth-body">

    <header class="site-header site-header-dark">
        <nav class="navbar">
            <div class="container-fluid px-3 px-lg-4 justify-content-between">
                <a class="navbar-brand d-flex align-items-center gap-2">
                    <img src="img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
                    <span class="brand-text brand-text-light">Clínica<br>Veterinaria<br>SIENRA</span>
                </a>

                <a class="nav-link icon-badge icon-badge-user" href="LoginHTML.php" aria-label="Cuenta">
                    <i class="bi bi-person-fill"></i>
                </a>
            </div>
        </nav>
    </header>

    <main class="auth-main d-flex align-items-center justify-content-center">
        <form class="auth-form" action="LoginHTML.php" method="post">
            <h1 class="auth-title">Iniciar sesión</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control auth-input" id="email" name="email" placeholder="nombre@correo.com" required>
            </div>

            <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
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
                <a href="#" class="auth-link">Olvidé mi contraseña</a>
            </div>

            <button type="submit" class="btn btn-cta auth-submit">Ingresar</button>

            <p class="auth-footer-text">
                ¿No tenés cuenta? <a href="RegistroHTML.php" class="auth-link">Registrate</a>
            </p>
        </form>
    </main>

</body>

</html>