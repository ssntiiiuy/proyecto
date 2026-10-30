<?php
$rolesPermitidos = ['Veterinario', 'Cajero', 'Peluquero', 'Cirujano'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Usuario.php';

$busqueda = trim($_GET['busqueda'] ?? '');

$datosUsuarios = Usuario::listadoUsuarios($busqueda);
$clientes = $datosUsuarios['clientes'];
$empleados = $datosUsuarios['empleados'];

$error = null;
$datosForm = []; /* se guardan los datos del formulario para repoblarlo en caso de error */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $ci = trim($_POST['ci']);
  $nombre = trim($_POST['nombre']);
  $apellido = trim($_POST['apellido']);
  $correo = trim($_POST['correo']);
  $telefono = trim($_POST['telefono']);
  $direccion = trim($_POST['direccion']);
  $contraseña = $_POST['contrasena'];
  $tipoUsuario = 'cliente';
  $rol = null;

  if (empty($error)) {
    $usuario = new Usuario();
    $resultado = $usuario->registrar($ci, $nombre, $apellido, $correo, $telefono, $direccion, $contraseña, $tipoUsuario, $rol);

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

  <div class="admin-wrapper" data-bs-theme="light">
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

        <!-- listado de usuarios -->
        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <ul class="nav admin-tabs" role="tablist">
              <h2>Listado de clientes</h2>
            </ul>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuario">
              <i class="bi bi-plus-lg"></i> Agregar cliente
            </button>

          </div>
              <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4"
      action="usuarios.php" method="get">

    <input class="form-control"
           type="text"
           name="busqueda"
           placeholder="Buscar por C.I., nombre, apellido o correo..."
           value="<?= htmlspecialchars($busqueda) ?>">

    <button class="btn admin-btn-agregar ms-2" type="submit">
        <i class="bi bi-search"></i>
    </button>

    <?php if ($busqueda !== ''): ?>
        <a class="btn admin-btn-cancelar ms-2"
           href="usuarios.php">
            Limpiar
        </a>
    <?php endif; ?>
</form>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="panelClientes" role="tabpanel" aria-labelledby="tabClientes" tabindex="0">
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
              <input class="form-control" type="text" id="ci" name="ci" placeholder="45678901" inputmode="numeric" pattern="[0-9]{7,8}" value="<?= htmlspecialchars($datosForm['ci'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="telefono">Teléfono</label>
              <input class="form-control" type="tel" id="telefono" name="telefono" placeholder="097 654 321" value="<?= htmlspecialchars($datosForm['telefono'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="nombre">Nombre</label>
              <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Jorge" value="<?= htmlspecialchars($datosForm['nombre'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="apellido">Apellido</label>
              <input class="form-control" type="text" id="apellido" name="apellido" placeholder="Pérez" value="<?= htmlspecialchars($datosForm['apellido'] ?? '') ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="direccion">Dirección</label>
              <input class="form-control" type="text" id="direccion" name="direccion" placeholder="18 de Julio 1450" value="<?= htmlspecialchars($datosForm['direccion'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="correo">Correo</label>
              <input class="form-control" type="email" id="correo" name="correo" placeholder="nombre@correo.com" value="<?= htmlspecialchars($datosForm['correo'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="contrasena">Contraseña</label>
              <input class="form-control" type="password" id="contrasena" name="contrasena" required>
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