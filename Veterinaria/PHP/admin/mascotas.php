<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

$error = null;

/* eliminar: solo se borra por POST, despues de confirmar */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
  $mascota = new Mascota();
  $resultado = $mascota->eliminarMascota((int) $_POST['IdMascota']);
  header("Location: mascotas.php?msg=" . $resultado);
  exit();
}

/* mensajes fijos que se muestran despues de editar o eliminar */
$mensajes = [
  'editado' => ['success', 'Los cambios de la mascota se guardaron.'],
  'eliminado' => ['success', 'La mascota se eliminó.'],
  'bloqueado' => ['danger', 'No se puede eliminar la mascota porque tiene citas, historial clínico, carnet de vacunación o servicios registrados.'],
  'noexiste' => ['danger', 'La mascota no existe.'],
  'error' => ['danger', 'No se pudo completar la operación.']
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

/* confirmacion: mascotas.php?eliminar=Id muestra el aviso arriba del listado */
$mascotaEliminar = null;
if (isset($_GET['eliminar'])) {
  $mascotaEliminar = Mascota::buscarPorId((int) $_GET['eliminar']);
  if (!$mascotaEliminar) {
    $mensaje = $mensajes['noexiste'];
  }
}

/* funcion para guardar los datos */
$busqueda = trim($_GET['busqueda'] ?? '');
$datosMascotas = Mascota::listadoMascotas($busqueda);
$mascotas = $datosMascotas['mascotas'];

$conexion = (new Conexion())->establecerConexion();

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
      $mascota = new Mascota();
      $resultado = $mascota->crearMascota($nombre, $especie, $raza, $fechaNacimiento, $ciDueño);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: mascotas.php");
      exit();
    } else {
      $error = "No se pudo guardar la mascota. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mascotas - Panel Sienra</title>

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
    $paginaActiva = 'mascotas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Mascotas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($mascotaEliminar) { ?>
          <div class="alert alert-warning" role="alert">
            <form class="d-flex flex-wrap align-items-center gap-2" action="mascotas.php" method="post">
              <span class="me-auto">
                ¿Eliminar la mascota <strong><?= htmlspecialchars($mascotaEliminar['Nombre']) ?></strong> (Id <?= $mascotaEliminar['IdMascota'] ?>)? Esta acción no se puede deshacer.
              </span>
              <input type="hidden" name="IdMascota" value="<?= $mascotaEliminar['IdMascota'] ?>">
              <a class="btn btn-sm admin-btn-cancelar" href="mascotas.php">Cancelar</a>
              <button type="submit" class="btn btn-sm btn-danger" name="eliminar">Sí, eliminar</button>
            </form>
          </div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de mascotas</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalMascota">
              <i class="bi bi-plus-lg"></i> Agregar mascota
            </button>
          </div>

          <!-- listado por parametros -->
          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="mascotas.php" method="get">
            <input class="form-control" type="text" name="busqueda" placeholder="Buscar por C.I. o nombre..."
                   value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn admin-btn-agregar ms-2" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($busqueda !== ''): ?>
              <a class="btn admin-btn-cancelar ms-2" href="mascotas.php">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Especie</th>
                  <th scope="col">Raza</th>
                  <th scope="col">Fecha de nacimiento</th>
                  <th scope="col">Dueño</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($mascotas)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No hay mascotas registradas.</td>
                  </tr>
                  <?php else: foreach ($mascotas as $masc): ?>

                    <tr>
                      <td><?= htmlspecialchars($masc['IdMascota']) ?></td>
                      <td><?= htmlspecialchars($masc['Nombre']) ?></td>
                      <td><?= htmlspecialchars($masc['Especie']) ?></td>
                      <td><?= htmlspecialchars($masc['Raza']) ?></td>
                      <td><?= $masc['FechaNacimiento'] ? date('d/m/Y', strtotime($masc['FechaNacimiento'])) : '—' ?></td>
                      <td><?= htmlspecialchars($masc['NombreDueño'] . ' ' . $masc['ApellidoDueño']) ?><br><span class="admin-subtexto">CI: <?= htmlspecialchars($masc['CI']) ?></span></td>
                      <td class="text-end">
                        <a class="btn btn-sm admin-btn-icono" href="fichaMascota.php?id=<?= $masc['IdMascota'] ?>" aria-label="Ver ficha"><i class="bi bi-file-earmark-text"></i></a>
                        <a class="btn btn-sm admin-btn-icono" href="editar_mascota.php?id=<?= $masc['IdMascota'] ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                        <a class="btn btn-sm admin-btn-icono admin-btn-eliminar" href="mascotas.php?eliminar=<?= $masc['IdMascota'] ?>" aria-label="Eliminar"><i class="bi bi-trash"></i></a>
                      </td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- agregar mascota -->
  <div class="modal fade admin-modal" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalMascotaTitulo">Agregar mascota</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
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
                <option value="" <?= empty($datosForm['CI']) ? 'selected' : '' ?> disabled>Elegir cliente</option>
                <?php
                $stmtCli = $conexion->query("SELECT u.CI, u.Nombre, u.Apellido FROM CLIENTE c INNER JOIN USUARIO u ON c.CI = u.CI");
                while ($cli = $stmtCli->fetch(PDO::FETCH_ASSOC)):
                ?>
                  <option value="<?= $cli['CI'] ?>" <?= (($datosForm['CI'] ?? '') == $cli['CI']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']) ?> (CI: <?= $cli['CI'] ?>)
                  </option>
                <?php endwhile; ?>
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
      new bootstrap.Modal(document.getElementById('modalMascota')).show();
    </script>
  <?php endif; ?>

</body>

</html>