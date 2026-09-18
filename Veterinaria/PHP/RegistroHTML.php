    <?php
    require_once "Login.php";
    require_once "Usuario.php";

    if (isset($_POST['boton'])) {
        $ci = ($_POST['ci']);
        $nombre = ($_POST['nombre']);
        $apellido = ($_POST['apellido']);
        $correo = ($_POST['correo']);
        $telefono = ($_POST['telefono']);
        $calle1 = ($_POST['calle1']);
        $calle2 = ($_POST['calle2']);
        $contraseña = ($_POST['password']);

        $resultado = (new Usuario())->registrar($ci, $nombre, $apellido, $telefono, $calle1, $calle2);

        if ($resultado == true) {
            session_start();
            $respuesta = "Ingresaste correctamente";
            $_SESSION["correo"] = $correo;
            header("Location: ../index.php");
        } else {
            $respuesta = "Error al ingresar";
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
            /* Ancho adaptado para contener 2 columnas cómodamente */
            .auth-form-wide {
                width: 100%;
                max-width: 560px;
            }
        </style>
    </head>

    <body class="auth-body">

        <!-- ===================== HEADER (invitado) ===================== -->
        <header class="site-header site-header-dark">
            <nav class="navbar">
                <div class="container-fluid px-3 px-lg-4 justify-content-between">
                    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                        <img src="img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
                        <span class="brand-text brand-text-light">Clínica<br>Veterinaria<br>SIENRA</span>
                    </a>

                    <a class="nav-link icon-badge icon-badge-user" href="loginHTML.php" aria-label="Cuenta">
                        <i class="bi bi-person-fill"></i>
                    </a>
                </div>
            </nav>
        </header>

        <!-- ===================== FORM REGISTRO COMPACTO ===================== -->
        <main class="auth-main d-flex align-items-center justify-content-center py-3">
            <form class="auth-form auth-form-wide" action="#" method="post" novalidate>
                <h1 class="auth-title mb-3 text-center">Crear cuenta</h1>

                <div class="row g-2">
                    <!-- Fila 1 -->
                    <div class="col-6 mb-2">
                        <label for="ci" class="form-label">C.I.</label>
                        <input type="text" class="form-control auth-input" id="ci" name="ci" placeholder="1.234.567-8" required>
                    </div>
                    <div class="col-6 mb-2">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control auth-input" id="telefono" name="telefono" placeholder="097 654 321" required>
                    </div>

                    <!-- Fila 2 -->
                    <div class="col-6 mb-2">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control auth-input" id="nombre" name="nombre" placeholder="Jorge" required>
                    </div>
                    <div class="col-6 mb-2">
                        <label for="apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control auth-input" id="apellido" name="apellido" placeholder="Pérez" required>
                    </div>

                    <!-- Fila 3 -->
                    <div class="col-12 mb-2">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control auth-input" id="correo" name="correo" placeholder="jorgeperez@gmail.com" required>
                    </div>

                    <!-- Fila 4 -->
                    <div class="col-6 mb-2">
                        <label for="calle1" class="form-label">Calle 1</label>
                        <input type="text" class="form-control auth-input" id="calle1" name="calle1" placeholder="18 de Julio" required>
                    </div>
                    <div class="col-6 mb-2">
                        <label for="calle2" class="form-label">Calle 2</label>
                        <input type="text" class="form-control auth-input" id="calle2" name="calle2" placeholder="Arturo Santana">
                    </div>

                    <!-- Fila 5: Contraseñas -->
                    <div class="col-6 mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control auth-input" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="confirm_password" class="form-label">Repetir contraseña</label>
                        <input type="password" class="form-control auth-input" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-cta auth-submit mt-2">Crear cuenta</button>

                <p class="auth-footer-text mt-3 mb-0">
                    ¿Ya tenés cuenta? <a href="loginHTML.php" class="auth-link">Iniciá sesión</a>
                </p>
            </form>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/main.js"></script>
    </body>

    </html>