<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Usuario.php';

$datosUsuarios = Usuario::listadoUsuarios();

/* funcion para guardar los datos */
$clientes = $datosUsuarios['clientes'];
$empleados = $datosUsuarios['empleados'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $ci          = trim($_POST['ci']);
  $nombre      = trim($_POST['nombre']);
  $apellido    = trim($_POST['apellido']);
  $correo      = trim($_POST['correo']);
  $telefono    = trim($_POST['telefono']);
  $direccion   = trim($_POST['direccion']);
  $contraseña  = $_POST['contrasena'];
  $tipoUsuario = $_POST['tipo_usuario'];

  $usuario = new Usuario();
  $resultado = $usuario->registrar($ci, $nombre, $apellido, $correo, $telefono, $direccion, $contraseña, $tipoUsuario);

  if ($resultado) {
    $_SESSION["correo"] = $correo;
    header("Location: usuarios.php");
    exit();
  } else {
    $error = "No se pudo completar el registro. Verifica si el correo o la C.I. ya existen.";
    /* para que no se borren los datos */
    $_POST['ci'] = $ci;
    $_POST['nombre'] = $nombre;
    $_POST['apellido'] = $apellido;
    $_POST['correo'] = $correo;
    $_POST['telefono'] = $telefono;
    $_POST['direccion'] = $direccion;
    $_POST['tipo_usuario'] = $tipoUsuario;
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

        <!-- listado de usuarios -->
        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <ul class="nav admin-tabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tabClientes" data-bs-toggle="tab" data-bs-target="#panelClientes" type="button" role="tab" aria-controls="panelClientes" aria-selected="true">Clientes</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabEmpleados" data-bs-toggle="tab" data-bs-target="#panelEmpleados" type="button" role="tab" aria-controls="panelEmpleados" aria-selected="false">Empleados</button>
              </li>
            </ul>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalUsuario">
              <i class="bi bi-plus-lg"></i> Agregar usuario
            </button>
          </div>

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
                      <th scope="col" class="text-end">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php foreach ($clientes as $cli): ?>

                      <tr>
                        <td><?= htmlspecialchars($cli['CI']) ?></td>
                        <td><?= htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']) ?></td>
                        <td class="d-none d-xxl-table-cell"><?= htmlspecialchars($cli['Direccion']) ?></td>
                        <td><?= $cli['Telefonos'] ?></td>
                        <td><?= htmlspecialchars($cli['Correo']) ?></td>
                        <td><?= date('d/m/Y', strtotime($cli['FechaCreado'])) ?></td>
                        <td class="text-end">
                          <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                          <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="tab-pane fade" id="panelEmpleados" role="tabpanel" aria-labelledby="tabEmpleados" tabindex="0">
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
                    Usuario::ListadoUsuarios($empleados);
                    foreach ($empleados as $emp):
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($emp['CI']) ?></td>
                        <td><?= htmlspecialchars($emp['Nombre'] . ' ' . $emp['Apellido']) ?></td>
                        <td class="d-none d-xxl-table-cell"><?= htmlspecialchars($emp['Direccion']) ?></td>
                        <td><?= $emp['Telefonos'] ?></td>
                        <td><?= htmlspecialchars($emp['Correo']) ?></td>
                        <td><?= htmlspecialchars($emp['Rol'] ?? 'Empleado') ?></td>
                        <td><?= date('d/m/Y', strtotime($emp['FechaCreado'])) ?></td>
                        <td class="text-end">
                          <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                          <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
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
  <div class="modal fade admin-modal" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalUsuarioTitulo">Agregar usuario</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label" for="ci">C.I.</label>
              <input class="form-control" type="text" id="ci" name="ci" placeholder="45678901" inputmode="numeric" pattern="[0-9]{7,8}" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="telefono">Teléfono</label>
              <input class="form-control" type="tel" id="telefono" name="telefono" placeholder="097 654 321" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="nombre">Nombre</label>
              <input class="form-control" type="text" id="nombre" name="nombre" placeholder="Jorge" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="apellido">Apellido</label>
              <input class="form-control" type="text" id="apellido" name="apellido" placeholder="Pérez" required>
            </div>
            <div class="col-12">
              <label class="form-label" for="direccion">Dirección</label>
              <input class="form-control" type="text" id="direccion" name="direccion" placeholder="18 de Julio 1450">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="correo">Correo</label>
              <input class="form-control" type="email" id="correo" name="correo" placeholder="nombre@correo.com" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="contrasena">Contraseña</label>
              <input class="form-control" type="password" id="contrasena" name="contrasena" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="tipo_usuario">Tipo de usuario</label>
              <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                <option value="" selected disabled>Elegir tipo</option>
                <option value="cliente">Cliente</option>
                <option value="empleado">Empleado</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Rol">Rol <span class="admin-aclaracion">(solo empleados)</span></label>
              <input class="form-control" type="text" id="Rol" name="Rol" placeholder="Veterinario">
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
</body>

</html>