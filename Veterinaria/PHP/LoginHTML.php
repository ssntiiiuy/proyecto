    <?php
    require_once "Login.php";

    if (isset($_POST['boton'])) {
        $username = ($_POST['username']);
        $pass = ($_POST['pass']);

        $resultado = (new Login())->validar($username, $pass);

        if ($resultado == true) {
            session_start();
            $respuesta = "Ingresaste correctamente";
            $_SESSION["username"] = $username;
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

        <!-- ===================== FORM LOGIN ===================== -->
        <main class="auth-main d-flex align-items-center justify-content-center">
            <form class="auth-form" action="#" method="get" novalidate>
                <h1 class="auth-title">Iniciar sesión</h1>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control auth-input" id="email" name="email" placeholder="nombre@correo.com" required>
                </div>

                <div class="mb-2">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <input type="password" class="form-control auth-input" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                        <button class="btn auth-eye-btn" type="button" id="togglePassword" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <a href="#" class="auth-link">Olvidé mi contraseña</a>
                </div>

                <button type="submit" class="btn btn-cta auth-submit">Ingresar</button>

                <p class="auth-footer-text">
                    ¿No tenés cuenta? <a href="registroHTML.php" class="auth-link">Registrate</a>
                </p>
            </form>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const authForm = document.querySelector('.auth-form');

            togglePassword.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
            });

            authForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const email = document.getElementById('email').value.trim().toLowerCase();
                const password = document.getElementById('password').value;

                const validEmail = 'admin@clinica.com';
                const validPassword = '123456';

                if (email === validEmail && password === validPassword) {
                    window.location.href = 'index.php';
                    return;
                }

                alert('Credenciales inválidas. Usa admin@clinica.com / 123456');
            });
        </script>
    </body>

    </html>