<?php
session_start();

require_once __DIR__ . '/clases/Conexion.php';
require_once __DIR__ . '/clases/Login.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email'] ?? '');

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Ingresá un correo válido.";
    } else {
        /* se borra cualquier código anterior */
        unset($_SESSION['recuperar']);

        $login = new Login();
        if ($login->existeCorreo($correo)) {
            $codigo = (string) random_int(100000, 999999);

            $_SESSION['recuperar'] = [
                'correo'   => $correo,
                'codigo'   => $codigo,
                'vence'    => time() + 15 * 60,
                'intentos' => 0
            ];

            require __DIR__ . '/config/correo.php';

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $correoUsuario;
                $mail->Password   = $correoContrasena;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($correoUsuario, $correoNombre);
                $mail->addAddress($correo);

                $mail->isHTML(true);
                $mail->Subject = 'Código para cambiar tu contraseña - Clínica Veterinaria Sienra';
                $mail->Body    = '<p>Hola,</p>'
                    . '<p>Recibimos un pedido para cambiar la contraseña de tu cuenta en la <strong>Clínica Veterinaria Sienra</strong>.</p>'
                    . '<p>Tu código es: <strong style="font-size: 22px; letter-spacing: 4px;">' . $codigo . '</strong></p>'
                    . '<p>El código vence en 15 minutos.</p>'
                    . '<p>Si no pediste el cambio, ignorá este correo: tu contraseña sigue igual.</p>'
                    . '<p>Clínica Veterinaria Sienra</p>';
                $mail->AltBody = "Hola,\n\n"
                    . "Recibimos un pedido para cambiar la contraseña de tu cuenta en la Clínica Veterinaria Sienra.\n\n"
                    . "Tu código es: " . $codigo . "\n\n"
                    . "El código vence en 15 minutos.\n"
                    . "Si no pediste el cambio, ignorá este correo: tu contraseña sigue igual.\n\n"
                    . "Clínica Veterinaria Sienra";

                $mail->send();
            } catch (Exception $e) {
                /* no se avisa en pantalla para no revelar si el correo existe; queda en el log de Apache */
                error_log("Recuperar contraseña: no se pudo enviar el correo: " . $mail->ErrorInfo);
            }
        }

        /* el mismo mensaje exista o no el correo */
        $_SESSION['aviso_codigo'] = "Si el correo está registrado, te enviamos un código.";
        header("Location: VerificarCodigoHTML.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Clínica Veterinaria Sienra</title>

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
                <a class="navbar-brand d-flex align-items-center gap-2" href="LoginHTML.php">
                    <img src="../img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
                    <span class="brand-text brand-text-light">Clínica<br>Veterinaria<br>SIENRA</span>
                </a>

                <a class="nav-link icon-badge icon-badge-user" href="LoginHTML.php" aria-label="Cuenta">
                    <i class="bi bi-person-fill"></i>
                </a>
            </div>
        </nav>
    </header>

    <main class="auth-main d-flex align-items-center justify-content-center">
        <form class="auth-form" action="RecuperarHTML.php" method="post">
            <h1 class="auth-title">Recuperar contraseña</h1>

            <?php if (!empty($error)) { ?>
                <div class="alert alert-danger py-2 text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php } ?>

            <p class="auth-footer-text mt-0 mb-3">
                Escribí el correo de tu cuenta y te enviamos un código de 6 dígitos para cambiar la contraseña.
            </p>

            <div class="mb-4">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control auth-input" id="email" name="email" placeholder="nombre@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-cta auth-submit">Enviar código</button>

            <p class="auth-footer-text">
                <a href="VerificarCodigoHTML.php" class="auth-link">Ya tengo un código</a>
                ·
                <a href="LoginHTML.php" class="auth-link">Volver a iniciar sesión</a>
            </p>
        </form>
    </main>

</body>

</html>
