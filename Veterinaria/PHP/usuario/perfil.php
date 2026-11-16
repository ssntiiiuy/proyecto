<?php
$rolesPermitidos = ['cliente'];
$rutaLogin = '../LoginHTML.php?accion=perfil';
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/includes/carrito.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../clases/Login.php';

// códigos para comprobar que cada formulario corresponde a esta sesión
$_SESSION['tokenPerfilDatos'] = $_SESSION['tokenPerfilDatos'] ?? bin2hex(random_bytes(32));
$_SESSION['tokenPerfilClave'] = $_SESSION['tokenPerfilClave'] ?? bin2hex(random_bytes(32));

$errorDatos = null;
$errorClave = null;

try {
  $conexion = (new Conexion())->establecerConexion();

  // busco la cedula del usuario a partir del correo guardado en la sesion
  $sqlUsuario = "SELECT u.CI FROM USUARIO u INNER JOIN HACE h ON u.CI = h.CI WHERE h.Correo = :correo";
  $stmt = $conexion->prepare($sqlUsuario);
  $stmt->execute([':correo' => $_SESSION['correo']]);
  $fila = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$fila) {
    header('Location: ../LoginHTML.php');
    exit();
  }
  $ci = (int) $fila['CI'];

  // actualizar nombre, apellido, teléfono y dirección
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardarDatos'])) {
    if (!hash_equals($_SESSION['tokenPerfilDatos'], (string) ($_POST['tokenPerfilDatos'] ?? ''))) {
      $errorDatos = 'No se pudo validar el formulario. Volvé a intentar.';
    } else {
      $nombre = trim($_POST['nombre'] ?? '');
      $apellido = trim($_POST['apellido'] ?? '');
      $telefono = trim($_POST['telefono'] ?? '');
      $direccion = trim($_POST['direccion'] ?? '');

      if ($nombre === '' || $apellido === '' || $direccion === '') {
        $errorDatos = 'Completá nombre, apellido y dirección.';
      } elseif ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
        $errorDatos = 'Ingresá un teléfono válido.';
      } else {
        $telefonos = $telefono !== '' ? [$telefono] : [];
        $usuario = new Usuario();
        if ($usuario->editarUsuario($ci, $nombre, $apellido, $direccion, $telefonos, null, null)) {
          header('Location: perfil.php?actualizado=1');
          exit();
        }
        $errorDatos = 'No se pudieron guardar los cambios. Intentá de nuevo.';
      }
    }
  }

  // cambiar la contraseña, pidiendo primero la actual
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiarClave'])) {
    if (!hash_equals($_SESSION['tokenPerfilClave'], (string) ($_POST['tokenPerfilClave'] ?? ''))) {
      $errorClave = 'No se pudo validar el formulario. Volvé a intentar.';
    } else {
      $actual = (string) ($_POST['claveActual'] ?? '');
      $nueva = (string) ($_POST['claveNueva'] ?? '');
      $confirmar = (string) ($_POST['claveConfirmar'] ?? '');

      $login = new Login();
      if (!$login->validar($_SESSION['correo'], $actual)) {
        $errorClave = 'La contraseña actual no es correcta.';
      } elseif ($nueva === '') {
        $errorClave = 'Ingresá la nueva contraseña.';
      } elseif ($nueva !== $confirmar) {
        $errorClave = 'Las contraseñas nuevas no coinciden.';
      } else {
        if ($login->cambiarContrasena($_SESSION['correo'], $nueva)) {
          header('Location: perfil.php?clave=1');
          exit();
        }
        $errorClave = 'No se pudo cambiar la contraseña. Intentá de nuevo.';
      }
    }
  }

  // traigo los datos ya guardados para mostrarlos en el formulario
  $usuarioActual = Usuario::buscarPorCi($ci);
} catch (PDOException $e) {
  $usuarioActual = null;
}

$nombreUsuario = $usuarioActual['Nombre'] ?? 'Usuario';

// variables que necesita header.php
$paginaActiva = '';
$mostrarCarrito = true;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi perfil - Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css?v=<?= filemtime(__DIR__ . '/../../CSS/style.css') ?>">
  <script src="../../JS/tema.js"></script>
</head>

<body>

  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="dashboard">
    <div class="container-lg py-4">

      <div class="greeting-card">
        <span class="greeting-icon" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
        <div>
          <h1>Mi <span>perfil</span></h1>
          <p>Revisá y actualizá tus datos de contacto y tu contraseña.</p>
        </div>
      </div>

      <?php if (($_GET['actualizado'] ?? '') === '1') { ?>
        <div class="alert alert-success">Tus datos se actualizaron.</div>
      <?php } ?>
      <?php if (($_GET['clave'] ?? '') === '1') { ?>
        <div class="alert alert-success">Tu contraseña se actualizó.</div>
      <?php } ?>

      <?php if (!$usuarioActual) { ?>
        <div class="alert alert-danger">No se pudieron cargar tus datos. Volvé a intentar más tarde.</div>
      <?php } else { ?>

        <div class="row g-3">
          <div class="col-lg-6 d-flex flex-column gap-3">

            <div class="panel-card">
              <div class="panel-header">
                <h2><span class="panel-icon" aria-hidden="true"><i class="bi bi-person-vcard"></i></span> Mis datos</h2>
              </div>

              <?php if ($errorDatos) { ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($errorDatos) ?></div>
              <?php } ?>

              <form method="post" action="perfil.php">
                <input type="hidden" name="tokenPerfilDatos" value="<?= htmlspecialchars($_SESSION['tokenPerfilDatos']) ?>">

                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <input type="email" class="form-control auth-input" id="correo" value="<?= htmlspecialchars($usuarioActual['Correo'] ?? '') ?>" disabled>
                  </div>

                  <div class="col-sm-6">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text" class="form-control auth-input" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? $usuarioActual['Nombre'] ?? '') ?>" required>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="apellido">Apellido</label>
                    <input type="text" class="form-control auth-input" id="apellido" name="apellido" value="<?= htmlspecialchars($_POST['apellido'] ?? $usuarioActual['Apellido'] ?? '') ?>" required>
                  </div>

                  <div class="col-sm-6">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="tel" class="form-control auth-input" id="telefono" name="telefono" placeholder="097 654 321" pattern="[0-9]{8,9}" value="<?= htmlspecialchars($_POST['telefono'] ?? ($usuarioActual['telefonos'][0] ?? '')) ?>">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label" for="ci">C.I.</label>
                    <input type="text" class="form-control auth-input" id="ci" value="<?= htmlspecialchars((string) $ci) ?>" disabled>
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="direccion">Dirección</label>
                    <input type="text" class="form-control auth-input" id="direccion" name="direccion" value="<?= htmlspecialchars($_POST['direccion'] ?? $usuarioActual['Direccion'] ?? '') ?>" required>
                  </div>
                </div>

                <button type="submit" name="guardarDatos" class="btn btn-cta mt-3">Guardar cambios</button>
              </form>
            </div>

          </div>

          <div class="col-lg-6 d-flex flex-column gap-3">

            <div class="panel-card">
              <div class="panel-header">
                <h2><span class="panel-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span> Seguridad</h2>
              </div>

              <?php if ($errorClave) { ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($errorClave) ?></div>
              <?php } ?>

              <form method="post" action="perfil.php">
                <input type="hidden" name="tokenPerfilClave" value="<?= htmlspecialchars($_SESSION['tokenPerfilClave']) ?>">

                <div class="mb-2">
                  <label class="form-label" for="claveActual">Contraseña actual</label>
                  <div class="input-group">
                    <input type="checkbox" id="toggleClaveActual" class="d-none toggle-check">
                    <input type="text" class="form-control auth-input input-password" id="claveActual" name="claveActual" placeholder="******" required>
                    <label for="toggleClaveActual" class="btn auth-eye-btn d-flex align-items-center">
                      <i class="bi bi-eye icon-eye"></i>
                      <i class="bi bi-eye-slash icon-eye-slash"></i>
                    </label>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label" for="claveNueva">Nueva contraseña</label>
                  <div class="input-group">
                    <input type="checkbox" id="toggleClaveNueva" class="d-none toggle-check">
                    <input type="text" class="form-control auth-input input-password" id="claveNueva" name="claveNueva" placeholder="******" required>
                    <label for="toggleClaveNueva" class="btn auth-eye-btn d-flex align-items-center">
                      <i class="bi bi-eye icon-eye"></i>
                      <i class="bi bi-eye-slash icon-eye-slash"></i>
                    </label>
                  </div>
                </div>

                <div class="mb-2">
                  <label class="form-label" for="claveConfirmar">Repetir nueva contraseña</label>
                  <div class="input-group">
                    <input type="checkbox" id="toggleClaveConfirmar" class="d-none toggle-check">
                    <input type="text" class="form-control auth-input input-password" id="claveConfirmar" name="claveConfirmar" placeholder="******" required>
                    <label for="toggleClaveConfirmar" class="btn auth-eye-btn d-flex align-items-center">
                      <i class="bi bi-eye icon-eye"></i>
                      <i class="bi bi-eye-slash icon-eye-slash"></i>
                    </label>
                  </div>
                </div>

                <button type="submit" name="cambiarClave" class="btn btn-cta mt-2">Cambiar contraseña</button>
              </form>
            </div>

          </div>
        </div>

      <?php } ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>