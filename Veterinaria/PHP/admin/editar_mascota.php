<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

/* se busca la mascota que llega en la URL: editar_mascota.php?id=5 */
$id = (int) ($_GET['id'] ?? 0);
$mascota = Mascota::buscarPorId($id);

if (!$mascota) {
  header("Location: mascotas.php?msg=noexiste");
  exit();
}

$conexion = (new Conexion())->establecerConexion();

$error = null;
$datosForm = $mascota; /* el formulario arranca con los datos de la base */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $especie = trim($_POST['Especie'] ?? '');
  $raza = trim($_POST['Raza'] ?? '');
  $fechaNacimiento = trim($_POST['FechaNacimiento'] ?? '');
  $ciDueño = trim($_POST['CI'] ?? '');

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre de la mascota.";
  } elseif (!in_array($especie, ['Perro', 'Gato', 'Otro'])) {
    $error = "Elegí la especie de la mascota.";
  } elseif ($ciDueño === '') {
    $error = "Elegí el dueño de la mascota.";
  } elseif ($fechaNacimiento !== '' && strtotime($fechaNacimiento) === false) {
    $error = "La fecha de nacimiento no es válida.";
  } elseif (!empty($fechaNacimiento) && $fechaNacimiento > date('Y-m-d')) {
    $error = "La fecha de nacimiento no puede ser posterior a hoy.";
  } else {
    /* si la fecha queda vacia se guarda NULL */
    if ($fechaNacimiento === '') {
      $fechaNacimiento = null;
    }

    try {
      $resultado = (new Mascota())->editarMascota($id, $nombre, $especie, $raza, $fechaNacimiento, $ciDueño);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: mascotas.php?msg=editado");
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
  <title>Editar mascota - Panel Sienra</title>

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
    $paginaActiva = 'mascotas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Mascotas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert" data-bs-theme="dark"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card" data-bs-theme="dark">
          <div class="admin-card-header flex-wrap">
            <h2>Editar mascota (Id <?= $id ?>)</h2>
            <a class="admin-card-link" href="mascotas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_mascota.php?id=<?= $id ?>" method="post">
            <div class="row g-3">
              <div class="col-sm-12">
                <label class="form-label" for="Nombre">Nombre</label>
                <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Rex" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Especie">Especie</label>
                <select class="form-select" id="Especie" name="Especie" required>
                  <option value="" <?= empty($datosForm['Especie']) ? 'selected' : '' ?> disabled>Elegir especie</option>
                  <option value="Perro" <?= (($datosForm['Especie'] ?? '') === 'Perro') ? 'selected' : '' ?>>Perro</option>
                  <option value="Gato" <?= (($datosForm['Especie'] ?? '') === 'Gato') ? 'selected' : '' ?>>Gato</option>
                  <option value="Otro" <?= (($datosForm['Especie'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Raza">Raza</label>
                <input class="form-control" type="text" id="Raza" name="Raza" placeholder="Labrador" maxlength="100" value="<?= htmlspecialchars($datosForm['Raza'] ?? '') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="FechaNacimiento">Fecha de nacimiento</label>
                <input class="form-control" type="date" id="FechaNacimiento" name="FechaNacimiento"
                  max="<?= date('Y-m-d') ?>"
                  value="<?= htmlspecialchars($datosForm['FechaNacimiento'] ?? '') ?>">
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="CI">Dueño</label>
                <select class="form-select" id="CI" name="CI" required>
                  <option value="" disabled>Elegir cliente</option>
                  <?php
                  $stmtCli = $conexion->query("SELECT u.CI, u.Nombre, u.Apellido FROM CLIENTE c INNER JOIN USUARIO u ON c.CI = u.CI");
                  while ($cli = $stmtCli->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                    <option value="<?= $cli['CI'] ?>" <?= (($datosForm['CI'] ?? '') == $cli['CI']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']) ?> (CI: <?= $cli['CI'] ?>)
                    </option>
                  <?php } ?>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="mascotas.php">Cancelar</a>
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
