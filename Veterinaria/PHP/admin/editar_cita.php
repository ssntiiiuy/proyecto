<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Cita.php';

/* se busca la cita que llega en la URL: editar_cita.php?id=5 */
$id = (int) ($_GET['id'] ?? 0);
$cita = Cita::buscarPorId($id);

if (!$cita) {
  header("Location: citas.php?msg=noexiste");
  exit();
}

$conexion = (new Conexion())->establecerConexion();

$sqlServicios = $conexion->query("SELECT IdServicio, Nombre FROM SERVICIO ORDER BY Nombre");
$servicios = $sqlServicios->fetchAll(PDO::FETCH_ASSOC);

/* solo las mascotas del cliente de la cita */
$sqlMascotas = $conexion->prepare("SELECT IdMascota, Nombre FROM MASCOTA WHERE CI = :ci ORDER BY Nombre");
$sqlMascotas->execute([':ci' => $cita['CI']]);
$mascotasCliente = $sqlMascotas->fetchAll(PDO::FETCH_ASSOC);

$error = null;
$datosForm = $cita; /* el formulario arranca con los datos de la base */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $idMascota = (int) ($_POST['IdMascota'] ?? 0);
  $idServicio = (int) ($_POST['IdServicio'] ?? 0);
  $fecha = trim($_POST['Fecha'] ?? '');
  $hora = trim($_POST['Hora'] ?? '');
  $estado = trim($_POST['Estado'] ?? '');

  /* la mascota elegida tiene que ser del mismo cliente */
  $mascotaDelCliente = false;
  foreach ($mascotasCliente as $m) {
    if ($m['IdMascota'] == $idMascota) {
      $mascotaDelCliente = true;
    }
  }

  /* se valida antes de guardar */
  if (!$mascotaDelCliente) {
    $error = "La mascota elegida no es de este cliente.";
  } elseif ($idServicio === 0) {
    $error = "Elegí el servicio de la cita.";
  } elseif ($fecha === '') {
    $error = "Falta la fecha de la cita.";
  } elseif (strtotime($fecha) === false) {
    $error = "La fecha de la cita no es válida.";
  } elseif ($fecha !== $cita['Fecha'] && $fecha < date('Y-m-d')) {
    /* se puede dejar la fecha que ya tenia, pero no cambiarla a un dia pasado */
    $error = "La fecha no puede ser anterior a hoy.";
  } elseif ($hora === '') {
    $error = "Falta la hora de la cita.";
  } elseif (!in_array($estado, ['Pendiente', 'Confirmada', 'Cancelada'])) {
    $error = "Elegí un estado válido para la cita.";
  } else {
    try {
      $resultado = (new Cita())->editarCita($id, $idServicio, $idMascota, $fecha, $hora, $estado);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: citas.php?msg=editado");
      exit();
    } else {
      $error = "No se pudieron guardar los cambios. Revisá los datos.";
    }
  }
}

/* el minimo del calendario es hoy, o la fecha original si ya paso */
$fechaMinima = min(date('Y-m-d'), $cita['Fecha']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar cita - Panel Sienra</title>

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
    $paginaActiva = 'citas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Citas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert" data-bs-theme="dark"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card" data-bs-theme="dark">
          <div class="admin-card-header flex-wrap">
            <h2>Editar cita (Id <?= $id ?>)</h2>
            <a class="admin-card-link" href="citas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_cita.php?id=<?= $id ?>" method="post">
            <div class="mb-3">
              <span class="admin-subtexto">Cliente:</span>
              <strong><?= htmlspecialchars(($cita['ClienteNombre'] ?? '') . ' ' . ($cita['ClienteApellido'] ?? '')) ?></strong>
              <span class="admin-subtexto">(CI: <?= htmlspecialchars($cita['CI'] ?? '') ?>)</span>
            </div>

            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label" for="IdMascota">Mascota</label>
                <select class="form-select" id="IdMascota" name="IdMascota" required>
                  <option value="" disabled>Elegir mascota</option>
                  <?php foreach ($mascotasCliente as $m) { ?>
                    <option value="<?= $m['IdMascota'] ?>" <?= (($datosForm['IdMascota'] ?? '') == $m['IdMascota']) ? 'selected' : '' ?>><?= htmlspecialchars($m['Nombre']) ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="IdServicio">Servicio</label>
                <select class="form-select" id="IdServicio" name="IdServicio" required>
                  <option value="" disabled>Elegir servicio</option>
                  <?php foreach ($servicios as $s) { ?>
                    <option value="<?= $s['IdServicio'] ?>" <?= (($datosForm['IdServicio'] ?? '') == $s['IdServicio']) ? 'selected' : '' ?>><?= htmlspecialchars($s['Nombre']) ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Fecha">Fecha</label>
                <input class="form-control" type="date" id="Fecha" name="Fecha" min="<?= $fechaMinima ?>" value="<?= htmlspecialchars($datosForm['Fecha'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Hora">Hora</label>
                <input class="form-control" type="time" id="Hora" name="Hora" value="<?= htmlspecialchars(substr($datosForm['Hora'] ?? '', 0, 5)) ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Estado">Estado</label>
                <select class="form-select" id="Estado" name="Estado" required>
                  <option value="Pendiente" <?= (($datosForm['Estado'] ?? '') === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                  <option value="Confirmada" <?= (($datosForm['Estado'] ?? '') === 'Confirmada') ? 'selected' : '' ?>>Confirmada</option>
                  <option value="Cancelada" <?= (($datosForm['Estado'] ?? '') === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="citas.php">Cancelar</a>
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
