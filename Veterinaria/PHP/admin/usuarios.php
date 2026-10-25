<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Usuario.php';

/* se necesita la sesion para saber quien esta logueado (no se puede eliminar a si mismo) */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* pestaña que queda abierta: clientes o empleados */
$pestana = ($_GET['pestana'] ?? '') === 'empleados' ? 'empleados' : 'clientes';

/* eliminar: solo se borra por POST, despues de confirmar */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
  $ciBorrar = (int) $_POST['CI'];
  $usuarioBorrar = Usuario::buscarPorCi($ciBorrar);

  if (!$usuarioBorrar) {
    $resultado = 'noexiste';
  } elseif ($usuarioBorrar['Correo'] === ($_SESSION['correo'] ?? '')) {
    $resultado = 'propio';
  } else {
    $usuario = new Usuario();
    $resultado = $usuario->eliminarUsuario($ciBorrar);

    /* el mensaje de bloqueo depende de si es cliente o empleado */
    if ($resultado === 'bloqueado') {
      $resultado = $usuarioBorrar['CiCliente'] ? 'bloqueado_cliente' : 'bloqueado_empleado';
    }
  }

  header("Location: usuarios.php?" . http_build_query(['msg' => $resultado, 'pestana' => $pestana]));
  exit();
}

/* mensajes fijos que se muestran despues de editar o eliminar */
$mensajes = [
  'editado' => ['success', 'Los cambios del usuario se guardaron.'],
  'eliminado' => ['success', 'El usuario se eliminó.'],
  'bloqueado_cliente' => ['danger', 'No se puede eliminar el cliente porque tiene mascotas, citas o compras registradas.'],
  'bloqueado_empleado' => ['danger', 'No se puede eliminar el empleado porque tiene servicios asignados que brinda.'],
  'propio' => ['danger', 'No podés eliminar tu propio usuario mientras tenés la sesión iniciada.'],
  'noexiste' => ['danger', 'El usuario no existe.'],
  'error' => ['danger', 'No se pudo completar la operación.']
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

/* confirmacion: usuarios.php?eliminar=CI muestra el aviso arriba del listado */
$usuarioEliminar = null;
if (isset($_GET['eliminar'])) {
  $usuarioEliminar = Usuario::buscarPorCi((int) $_GET['eliminar']);
  if (!$usuarioEliminar) {
    $mensaje = $mensajes['noexiste'];
  } elseif ($usuarioEliminar['Correo'] === ($_SESSION['correo'] ?? '')) {
    $mensaje = $mensajes['propio'];
    $usuarioEliminar = null;
  }
}

$datosUsuarios = Usuario::listadoUsuarios();
$clientes = $datosUsuarios['clientes'];
$empleados = $datosUsuarios['empleados'];

$error = null;
$datosForm = []; /* se guardan los datos del formulario para repoblarlo en caso de error */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $ci = trim($_POST['ci'] ?? '');
  $nombre = trim($_POST['nombre'] ?? '');
  $apellido = trim($_POST['apellido'] ?? '');
  $correo = trim($_POST['correo'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $contraseña = $_POST['contrasena'] ?? '';
  $tipoUsuario = $_POST['tipo_usuario'] ?? '';
  $rol = null;

  /* se valida antes de guardar */
  if ($ci === '') {
    $error = "Falta la C.I. del usuario.";
  } elseif (!preg_match('/^[0-9]{7,8}$/', $ci)) {
    $error = "La C.I. debe tener 7 u 8 números, sin puntos ni guiones.";
  } elseif ($nombre === '') {
    $error = "Falta el nombre del usuario.";
  } elseif ($apellido === '') {
    $error = "Falta el apellido del usuario.";
  } elseif ($telefono === '') {
    $error = "Falta el teléfono del usuario.";
  } elseif (!preg_match('/^[0-9 ]{8,20}$/', $telefono)) {
    $error = "El teléfono solo puede tener números y espacios (entre 8 y 20).";
  } elseif ($correo === '') {
    $error = "Falta el correo del usuario.";
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $error = "El correo no es válido.";
  } elseif ($contraseña === '') {
    $error = "Falta la contraseña del usuario.";
  } elseif (!in_array($tipoUsuario, ['cliente', 'empleado', 'admin'])) {
    $error = "Elegí el tipo de usuario.";
  }

  /* asigna rol si es empleado, si es admin el rol es admin */
  if ($tipoUsuario === 'empleado') {
    if (empty($_POST['rol'])) {
      $error = "Debe seleccionar un rol para el empleado.";
    } else {
      $rol = trim($_POST['rol']);
    }
  } elseif ($tipoUsuario === 'admin') {
    $rol = 'admin';
  }

  if (empty($error)) {
    try {
      $usuario = new Usuario();
      $resultado = $usuario->registrar($ci, $nombre, $apellido, $correo, $telefono, $direccion, $contraseña, $tipoUsuario, $rol);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: usuarios.php");
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
  <title>Usuarios - Panel Sienra</title>

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

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($usuarioEliminar) { ?>
          <div class="alert alert-warning" role="alert">
            <form class="d-flex flex-wrap align-items-center gap-2" action="usuarios.php?pestana=<?= $pestana ?>" method="post">
              <span class="me-auto">
                ¿Eliminar al usuario <strong><?= htmlspecialchars($usuarioEliminar['Nombre'] . ' ' . $usuarioEliminar['Apellido']) ?></strong> (C.I. <?= $usuarioEliminar['CI'] ?>) con su login y sus teléfonos? Esta acción no se puede deshacer.
              </span>
              <input type="hidden" name="CI" value="<?= $usuarioEliminar['CI'] ?>">
              <a class="btn btn-sm admin-btn-cancelar" href="usuarios.php?pestana=<?= $pestana ?>">Cancelar</a>
              <button type="submit" class="btn btn-sm btn-danger" name="eliminar">Sí, eliminar</button>
            </form>
          </div>
        <?php } ?>

        <!-- listado de usuarios -->
        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <ul class="nav admin-tabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $pestana === 'clientes' ? 'active' : '' ?>" id="tabClientes" data-bs-toggle="tab" data-bs-target="#panelClientes" type="button" role="tab" aria-controls="panelClientes" aria-selected="<?= $pestana === 'clientes' ? 'true' : 'false' ?>">Clientes</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $pestana === 'empleados' ? 'active' : '' ?>" id="tabEmpleados" data-bs-toggle="tab" data-bs-target="#panelEmpleados" type="button" role="tab" aria-controls="panelEmpleados" aria-selected="<?= $pestana === 'empleados' ? 'true' : 'false' ?>">Empleados</button>
              </li>
            </ul>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuario">
              <i class="bi bi-plus-lg"></i> Agregar usuario
            </button>
          </div>

          <div class="tab-content">
            <div class="tab-pane fade <?= $pestana === 'clientes' ? 'show active' : '' ?>" id="panelClientes" role="tabpanel" aria-labelledby="tabClientes" tabindex="0">
              <div class="table-responsive">
                <table class="table admin-tabla mb-0">
                  <thead>
                    <tr>
                      <th scope="col">C.I.</th>
                      <th scope="col">Nombre</th>
                      <th scope="col" class="d-none d-xxl-table-cell">Dirección</th>
                      <th scope="col">Teléfono</th>
                      <th scope="col">Correo</th>
                      <th scope="col">Fecha de alta</th>
                      <th scope="col" class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php foreach ($clientes as $cli): ?>

                      <tr>
                        <td><?= htmlspecialchars($cli['CI']) ?></td>
                        <td><?= htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']) ?></td>
                        <td class="d-none d-xxl-table-cell"><?= htmlspecialchars($cli['Direccion']) ?></td>
                        <td><?= nl2br(htmlspecialchars(str_replace('<br>', "\n", $cli['Telefonos'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars($cli['Correo']) ?></td>
                        <td><?= date('d/m/Y', strtotime($cli['FechaCreado'])) ?></td>
                        <td class="text-end">
                          <a class="btn btn-sm admin-btn-icono" href="editar_usuario.php?ci=<?= $cli['CI'] ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                          <a class="btn btn-sm admin-btn-icono admin-btn-eliminar" href="usuarios.php?eliminar=<?= $cli['CI'] ?>&amp;pestana=clientes" aria-label="Eliminar"><i class="bi bi-trash"></i></a>
                        </td>
                      </tr>
                    <?php endforeach; ?>

                  </tbody>
                </table>
              </div>
            </div>
            <div class="tab-pane fade <?= $pestana === 'empleados' ? 'show active' : '' ?>" id="panelEmpleados" role="tabpanel" aria-labelledby="tabEmpleados" tabindex="0">
              <div class="table-responsive">
                <table class="table admin-tabla mb-0">
                  <thead>
                    <tr>
                      <th scope="col">C.I.</th>
                      <th scope="col">Nombre</th>
                      <th scope="col" class="d-none d-xxl-table-cell">Dirección</th>
                      <th scope="col">Teléfono</th>
                      <th scope="col">Correo</th>
                      <th scope="col">Rol</th>
                      <th scope="col">Fecha de alta</th>
                      <th scope="col" class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    foreach ($empleados as $emp):
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($emp['CI']) ?></td>
                        <td><?= htmlspecialchars($emp['Nombre'] . ' ' . $emp['Apellido']) ?></td>
                        <td class="d-none d-xxl-table-cell"><?= htmlspecialchars($emp['Direccion']) ?></td>
                        <td><?= nl2br(htmlspecialchars(str_replace('<br>', "\n", $emp['Telefonos'] ?? ''))) ?></td>
                        <td><?= htmlspecialchars($emp['Correo']) ?></td>
                        <td><?= htmlspecialchars($emp['Rol'] ?? 'Empleado') ?></td>
                        <td><?= date('d/m/Y', strtotime($emp['FechaCreado'])) ?></td>
                        <td class="text-end">
                          <a class="btn btn-sm admin-btn-icono" href="editar_usuario.php?ci=<?= $emp['CI'] ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                          <a class="btn btn-sm admin-btn-icono admin-btn-eliminar" href="usuarios.php?eliminar=<?= $emp['CI'] ?>&amp;pestana=empleados" aria-label="Eliminar"><i class="bi bi-trash"></i></a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Agregar usuario -->
  <div class="modal fade admin-modal" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalUsuarioTitulo">Agregar usuario</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">

          <!-- Mensaje de error -->
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label" for="ci">C.I.</label>
              <input class="form-control" type="text" id="ci" name="ci" placeholder="45678901" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" title="7 u 8 números, sin puntos ni guiones" value="<?= htmlspecialchars($datosForm['ci'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="telefono">Teléfono</label>
              <input class="form-control" type="tel" id="telefono" name="telefono" placeholder="097 654 321" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="<?= htmlspecialchars($datosForm['telefono'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="nombre">Nombre</label>
              <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Jorge" maxlength="50" value="<?= htmlspecialchars($datosForm['nombre'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="apellido">Apellido</label>
              <input class="form-control" type="text" id="apellido" name="apellido" placeholder="Pérez" maxlength="50" value="<?= htmlspecialchars($datosForm['apellido'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="direccion">Dirección</label>
              <input class="form-control" type="text" id="direccion" name="direccion" placeholder="18 de Julio 1450" maxlength="150" value="<?= htmlspecialchars($datosForm['direccion'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="correo">Correo</label>
              <input class="form-control" type="email" id="correo" name="correo" placeholder="nombre@correo.com" maxlength="100" value="<?= htmlspecialchars($datosForm['correo'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="contrasena">Contraseña</label>
              <input class="form-control" type="password" id="contrasena" name="contrasena" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="tipo_usuario">Tipo de usuario</label>
              <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                <option value="" <?= empty($datosForm['tipo_usuario']) ? 'selected' : '' ?> disabled>Elegir tipo</option>
                <option value="cliente" <?= (($datosForm['tipo_usuario'] ?? '') === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                <option value="empleado" <?= (($datosForm['tipo_usuario'] ?? '') === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                <option value="admin" <?= (($datosForm['tipo_usuario'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>

            <div class="col-sm-6">
              <label class="form-label" for="rol">Rol <span class="admin-aclaracion">(Completar solo si es empleado)</span></label>
              <select class="form-select" id="rol" name="rol">
                <option value="" <?= empty($datosForm['rol']) ? 'selected' : '' ?> disabled>Elegir rol</option>
                <option value="Veterinario" <?= (($datosForm['rol'] ?? '') === 'Veterinario') ? 'selected' : '' ?>>Veterinario</option>
                <option value="Cirujano" <?= (($datosForm['rol'] ?? '') === 'Cirujano') ? 'selected' : '' ?>>Cirujano</option>
                <option value="Peluquero" <?= (($datosForm['rol'] ?? '') === 'Peluquero') ? 'selected' : '' ?>>Peluquero</option>
                <option value="Cajero" <?= (($datosForm['rol'] ?? '') === 'Cajero') ? 'selected' : '' ?>>Cajero</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar</button>
        </div>
      </form>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($error): ?>
<script>
  new bootstrap.Modal(document.getElementById('modalUsuario')).show();
</script>
<?php endif; ?>

</body>

</html>