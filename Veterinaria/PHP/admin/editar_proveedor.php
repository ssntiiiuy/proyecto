<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Proveedor.php';

/* se busca el proveedor que llega en la URL: editar_proveedor.php?id=5 */
$id = (int) ($_GET['id'] ?? 0);
$proveedor = Proveedor::buscarPorId($id);

if (!$proveedor) {
  header("Location: proveedores.php?msg=noexiste");
  exit();
}

$error = null;
$datosForm = $proveedor; /* el formulario arranca con los datos de la base */
$telefonosForm = $proveedor['telefonos'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $empresa = trim($_POST['NombreEmpresa'] ?? '');

  /* se juntan los telefonos que no quedaron vacios (los vacios se borran) */
  $telefonos = [];
  foreach ($_POST['numTelProveedor'] ?? [] as $tel) {
    $tel = trim($tel);
    if ($tel !== '' && !in_array($tel, $telefonos)) {
      $telefonos[] = $tel;
    }
  }
  $telefonosForm = $telefonos;

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre de contacto del proveedor.";
  } elseif ($empresa === '') {
    $error = "Falta la empresa del proveedor.";
  } elseif (count($telefonos) === 0) {
    $error = "El proveedor tiene que tener al menos un teléfono.";
  } else {
    foreach ($telefonos as $tel) {
      if (!preg_match('/^[0-9 ]{8,20}$/', $tel)) {
        $error = "El teléfono \"" . $tel . "\" solo puede tener números y espacios (entre 8 y 20).";
      }
    }
  }

  if (empty($error)) {
    try {
      $resultado = (new Proveedor())->editarProveedor($id, $nombre, $empresa, $telefonos);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: proveedores.php?msg=editado");
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
  <title>Editar proveedor - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
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

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert" data-bs-theme="dark"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card" data-bs-theme="dark">
          <div class="admin-card-header flex-wrap">
            <h2>Editar proveedor (Id <?= $id ?>)</h2>
            <a class="admin-card-link" href="proveedores.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_proveedor.php?id=<?= $id ?>" method="post">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label" for="Nombre">Nombre de contacto</label>
                <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Carlos Núñez" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="NombreEmpresa">Empresa</label>
                <input class="form-control" type="text" id="NombreEmpresa" name="NombreEmpresa" placeholder="Nutripet S.A." maxlength="100" value="<?= htmlspecialchars($datosForm['NombreEmpresa'] ?? '') ?>" required>
              </div>

              <!-- un campo por cada telefono que ya tiene -->
              <?php foreach ($telefonosForm as $i => $tel) { ?>
                <div class="col-sm-6">
                  <label class="form-label" for="tel<?= $i ?>">Teléfono <?= $i + 1 ?></label>
                  <input class="form-control" type="tel" id="tel<?= $i ?>" name="numTelProveedor[]" placeholder="2604 1122" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="<?= htmlspecialchars($tel) ?>">
                </div>
              <?php } ?>

              <!-- campo vacio para agregar otro telefono -->
              <div class="col-sm-6">
                <label class="form-label" for="telNuevo">Agregar teléfono <span class="admin-aclaracion">(opcional)</span></label>
                <input class="form-control" type="tel" id="telNuevo" name="numTelProveedor[]" placeholder="2604 1122" pattern="[0-9 ]{8,20}" maxlength="20" title="Solo números y espacios, entre 8 y 20 caracteres" value="">
              </div>

              <div class="col-12">
                <p class="admin-subtexto mb-0">Para borrar un teléfono, dejá su campo vacío. Tiene que quedar al menos uno.</p>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="proveedores.php">Cancelar</a>
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
