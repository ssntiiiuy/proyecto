<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Proveedor.php';

/* eliminar: solo se borra por POST, despues de confirmar */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
  $proveedor = new Proveedor();
  $resultado = $proveedor->eliminarProveedor((int) $_POST['IdProveedor']);
  header("Location: proveedores.php?msg=" . $resultado);
  exit();
}

/* mensajes fijos que se muestran despues de editar o eliminar */
$mensajes = [
  'editado' => ['success', 'Los cambios del proveedor se guardaron.'],
  'eliminado' => ['success', 'El proveedor se eliminó.'],
  'bloqueado' => ['danger', 'No se puede eliminar el proveedor porque tiene productos asociados. Primero hay que eliminar esos productos o asignarles otro proveedor.'],
  'noexiste' => ['danger', 'El proveedor no existe.'],
  'error' => ['danger', 'No se pudo completar la operación.']
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

/* confirmacion: proveedores.php?eliminar=Id muestra el aviso arriba del listado */
$proveedorEliminar = null;
if (isset($_GET['eliminar'])) {
  $proveedorEliminar = Proveedor::buscarPorId((int) $_GET['eliminar']);
  if (!$proveedorEliminar) {
    $mensaje = $mensajes['noexiste'];
  }
}

$busqueda = trim($_GET['busqueda'] ?? '');
$datosProveedores = Proveedor::listadoProveedor($busqueda);
$proveedores = $datosProveedores['proveedores'];

$error = null;
$datosForm = []; /* se guardan los datos del formulario para repoblarlo en caso de error */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $empresa = trim($_POST['NombreEmpresa'] ?? '');
  $Telefono = trim($_POST['numTelProveedor'] ?? '');

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre de contacto del proveedor.";
  } elseif ($empresa === '') {
    $error = "Falta la empresa del proveedor.";
  } elseif ($Telefono === '') {
    $error = "Falta el teléfono del proveedor.";
  } elseif (!preg_match('/^[0-9 ]{8,20}$/', $Telefono)) {
    $error = "El teléfono solo puede tener números y espacios (entre 8 y 20).";
  }

  if (empty($error)) {
    try {
      $usuario = new Proveedor();
      $resultado = $usuario->crearProveedor($nombre, $empresa, $Telefono);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: proveedores.php");
      exit();
    } else {
      $error = "No se pudo guardar el proveedor. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proveedores - Panel Sienra</title>

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
    $paginaActiva = 'proveedores';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Proveedores';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($proveedorEliminar) { ?>
          <div class="alert alert-warning" role="alert">
            <form class="d-flex flex-wrap align-items-center gap-2" action="proveedores.php" method="post">
              <span class="me-auto">
                ¿Eliminar el proveedor <strong><?= htmlspecialchars($proveedorEliminar['NombreEmpresa']) ?></strong> (Id <?= $proveedorEliminar['IdProveedor'] ?>) y sus teléfonos? Esta acción no se puede deshacer.
              </span>
              <input type="hidden" name="IdProveedor" value="<?= $proveedorEliminar['IdProveedor'] ?>">
              <a class="btn btn-sm admin-btn-cancelar" href="proveedores.php">Cancelar</a>
              <button type="submit" class="btn btn-sm btn-danger" name="eliminar">Sí, eliminar</button>
            </form>
          </div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de proveedores</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalProveedor">
              <i class="bi bi-plus-lg"></i> Agregar proveedor
            </button>
          </div>

          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="proveedores.php" method="get">
            <input class="form-control" type="text" name="busqueda" placeholder="Buscar por nombre, empresa o producto..."
              value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn admin-btn-agregar ms-2" type="submit" title="Buscar"><i class="bi bi-search"></i></button>
            <?php if ($busqueda !== ''): ?>
              <a class="btn admin-btn-cancelar ms-2" href="proveedores.php">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Empresa</th>
                  <th scope="col">Teléfonos</th>
                  <th scope="col">Productos</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody>

                <?php foreach ($proveedores as $proveedor): ?>
                  <tr>
                    <td><?= htmlspecialchars($proveedor['IdProveedor']) ?></td>
                    <td><?= htmlspecialchars($proveedor['Nombre']) ?></td>
                    <td><?= htmlspecialchars($proveedor['NombreEmpresa']) ?></td>
                    <td>
                      <?php
                      $telefonos = $proveedor['Telefonos'] ? explode('|', $proveedor['Telefonos']) : [];
                      echo nl2br(htmlspecialchars(implode("\n", $telefonos)));
                      ?>
                    </td>
                    <td><?= htmlspecialchars($proveedor['Productos'] ?? '-') ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm admin-btn-icono" href="editar_proveedor.php?id=<?= $proveedor['IdProveedor'] ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                      <a class="btn btn-sm admin-btn-icono admin-btn-eliminar" href="proveedores.php?eliminar=<?= $proveedor['IdProveedor'] ?>" aria-label="Eliminar"><i class="bi bi-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>

              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- agregar proveedor -->
  <div class="modal fade admin-modal" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalProveedorTitulo">Agregar proveedor</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-12">
              <label class="form-label" for="Nombre">Nombre de contacto</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Carlos Núñez" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="NombreEmpresa">Empresa</label>
              <input class="form-control" type="text" id="NombreEmpresa" name="NombreEmpresa" placeholder="Nutripet S.A." maxlength="100" value="<?= htmlspecialchars($datosForm['NombreEmpresa'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="numTelProveedor">Teléfono</label>
              <input class="form-control" type="tel" id="numTelProveedor" name="numTelProveedor" placeholder="2604 1122" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="<?= htmlspecialchars($datosForm['numTelProveedor'] ?? '') ?>" required>
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
      new bootstrap.Modal(document.getElementById('modalProveedor')).show();
    </script>
  <?php endif; ?>

</body>

</html>