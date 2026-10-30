<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Usuario.php';

/* se busca el usuario que llega en la URL: editar_usuario.php?ci=45678901 */
$ci = (int) ($_GET['ci'] ?? 0);
$usuario = Usuario::buscarPorCi($ci);

if (!$usuario) {
  header("Location: usuarios.php?msg=noexiste");
  exit();
}

/* CI, correo y tipo salen de la base y no se pueden cambiar */
$tipoUsuario = $usuario['TipoUsuario'] ?? '';
$esEmpleado = ($tipoUsuario === 'empleado'); /* solo a los empleados se les cambia el rol */
$pestana = $usuario['CiCliente'] ? 'clientes' : 'empleados'; /* pestaña a la que se vuelve */
$roles = ['Veterinario', 'Cirujano', 'Peluquero', 'Cajero'];

$error = null;
$ciUsuario = $usuario['CI'];
$correoUsuario = $usuario['Correo'] ?? '—';
$telefonosForm = $usuario['telefonos'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $apellido = trim($_POST['Apellido'] ?? '');
  $direccion = trim($_POST['Direccion'] ?? '');
  $rol = $esEmpleado ? trim($_POST['Rol'] ?? '') : null;
  $nuevaContraseña = $_POST['NuevaContrasena'] ?? '';

  /* se juntan los telefonos que no quedaron vacios (los vacios se borran) */
  $telefonos = [];
  foreach ($_POST['numTelUsuario'] ?? [] as $tel) {
    $tel = trim($tel);
    if ($tel !== '' && !in_array($tel, $telefonos)) {
      $telefonos[] = $tel;
    }
  }
  $telefonosForm = $telefonos;

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre del usuario.";
  } elseif ($apellido === '') {
    $error = "Falta el apellido del usuario.";
  } elseif (count($telefonos) === 0) {
    $error = "El usuario tiene que tener al menos un teléfono.";
  } elseif ($esEmpleado && !in_array($rol, $roles)) {
    $error = "Debe seleccionar un rol para el empleado.";
  } else {
    foreach ($telefonos as $tel) {
      if (!preg_match('/^[0-9 ]{8,20}$/', $tel)) {
        $error = "El teléfono \"" . $tel . "\" solo puede tener números y espacios (entre 8 y 20).";
      }
    }
  }

  if (empty($error)) {
    /* si la contraseña queda vacia, no se cambia */
    if ($nuevaContraseña === '') {
      $nuevaContraseña = null;
    }

    try {
      $resultado = (new Usuario())->editarUsuario($ci, $nombre, $apellido, $direccion, $telefonos, $rol, $nuevaContraseña);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: usuarios.php?msg=editado&pestana=" . $pestana);
      exit();
    } else {
      $error = "No se pudieron guardar los cambios. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar usuario - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'usuarios';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Usuarios';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Editar usuario</h2>
            <a class="admin-card-link" href="usuarios.php?pestana=<?= $pestana ?>"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_usuario.php?ci=<?= $ci ?>" method="post">
            <!-- datos que no se pueden cambiar -->
            <div class="row g-3 mb-3">
              <div class="col-sm-4">
                <p class="admin-dato-label">C.I.</p>
                <p class="admin-dato-valor"><?= htmlspecialchars($ciUsuario) ?></p>
              </div>
              <div class="col-sm-4">
                <p class="admin-dato-label">Correo</p>
                <p class="admin-dato-valor"><?= htmlspecialchars($correoUsuario) ?></p>
              </div>
              <div class="col-sm-4">
                <p class="admin-dato-label">Tipo de usuario</p>
                <p class="admin-dato-valor"><?= htmlspecialchars(ucfirst($tipoUsuario)) ?></p>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label" for="Nombre">Nombre</label>
                <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Jorge" maxlength="50" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Apellido">Apellido</label>
                <input class="form-control" type="text" id="Apellido" name="Apellido" placeholder="Pérez" maxlength="50" value="<?= htmlspecialchars($datosForm['Apellido'] ?? '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="Direccion">Dirección</label>
                <input class="form-control" type="text" id="Direccion" name="Direccion" placeholder="18 de Julio 1450" maxlength="150" value="<?= htmlspecialchars($datosForm['Direccion'] ?? '') ?>">
              </div>

              <?php if ($esEmpleado) { ?>
                <div class="col-sm-6">
                  <label class="form-label" for="Rol">Rol</label>
                  <select class="form-select" id="Rol" name="Rol" required>
                    <option value="" disabled <?= in_array($datosForm['Rol'] ?? '', $roles) ? '' : 'selected' ?>>Elegir rol</option>
                    <?php foreach ($roles as $r) { ?>
                      <option value="<?= $r ?>" <?= (($datosForm['Rol'] ?? '') === $r) ? 'selected' : '' ?>><?= $r ?></option>
                    <?php } ?>
                  </select>
                </div>
              <?php } ?>

              <!-- un campo por cada telefono que ya tiene -->
              <?php foreach ($telefonosForm as $i => $tel) { ?>
                <div class="col-sm-6">
                  <label class="form-label" for="tel<?= $i ?>">Teléfono <?= $i + 1 ?></label>
                  <input class="form-control" type="tel" id="tel<?= $i ?>" name="numTelUsuario[]" placeholder="097 654 321" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="<?= htmlspecialchars($tel) ?>">
                </div>
              <?php } ?>

              <!-- campo vacio para agregar otro telefono -->
              <div class="col-sm-6">
                <label class="form-label" for="telNuevo">Agregar teléfono <span class="admin-aclaracion">(opcional)</span></label>
                <input class="form-control" type="tel" id="telNuevo" name="numTelUsuario[]" placeholder="097 654 321" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="">
              </div>

              <div class="col-12">
                <p class="admin-subtexto mb-0">Para borrar un teléfono, dejá su campo vacío. Tiene que quedar al menos uno.</p>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="NuevaContrasena">Nueva contraseña <span class="admin-aclaracion">(dejar vacío para no cambiarla)</span></label>
                <input class="form-control" type="password" id="NuevaContrasena" name="NuevaContrasena" autocomplete="new-password">
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="usuarios.php?pestana=<?= $pestana ?>">Cancelar</a>
              <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar cambios</button>
            </div>
          </form>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
