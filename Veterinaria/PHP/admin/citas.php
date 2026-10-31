<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Cita.php';

/* eliminar: solo se borra por POST, despues de confirmar */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
  $cita = new Cita();
  $resultado = $cita->eliminarCita((int) $_POST['IdCita']);
  header("Location: citas.php?msg=" . ($resultado ? 'eliminado' : 'error'));
  exit();
}

/* mensajes fijos que se muestran despues de editar o eliminar */
$mensajes = [
  'editado' => ['success', 'Los cambios de la cita se guardaron.'],
  'eliminado' => ['success', 'La cita se eliminó.'],
  'noexiste' => ['danger', 'La cita no existe.'],
  'error' => ['danger', 'No se pudo completar la operación.']
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

/* confirmacion: citas.php?eliminar=Id muestra el aviso arriba del listado */
$citaEliminar = null;
if (isset($_GET['eliminar'])) {
  $citaEliminar = Cita::buscarPorId((int) $_GET['eliminar']);
  if (!$citaEliminar) {
    $mensaje = $mensajes['noexiste'];
  }
}

$estado = $_GET['estado'] ?? null;
$filtro = $_GET['filtro'] ?? null;
$fechaFiltro = trim($_GET['fecha'] ?? '');

// Si el usuario buscó por fecha, filtramos por fecha. Si no, usamos el filtro de estado general.
if ($fechaFiltro !== '') {
    $citas = Cita::listadoCitasPorFecha($fechaFiltro);
} else {
    $datosCitas = Cita::listadoCita($estado, $filtro);
    $citas = $datosCitas['citas'];
}

$conexion = (new Conexion())->establecerConexion();

$sqlServicios = $conexion->query("SELECT IdServicio, Nombre FROM SERVICIO ORDER BY Nombre");
$servicios = $sqlServicios->fetchAll(PDO::FETCH_ASSOC);

$clienteEncontrado = null;
$mascotasCliente = [];
$errorCedula = null;

/* se pide la cedula primero */
if (isset($_GET['ci']) && trim($_GET['ci']) !== '') {
  $ciBuscada = trim($_GET['ci']);

  $sqlCliente = $conexion->prepare("SELECT u.CI, u.Nombre, u.Apellido FROM CLIENTE c INNER JOIN USUARIO u ON c.CI = u.CI WHERE c.CI = :ci");
  $sqlCliente->execute([':ci' => $ciBuscada]);
  $clienteEncontrado = $sqlCliente->fetch(PDO::FETCH_ASSOC);

  if (!$clienteEncontrado) {
    $errorCedula = "No existe un cliente con esa cédula.";
  } else {
    $sqlMascotas = $conexion->prepare("SELECT IdMascota, Nombre FROM MASCOTA WHERE CI = :ci ORDER BY Nombre");
    $sqlMascotas->execute([':ci' => $ciBuscada]);
    $mascotasCliente = $sqlMascotas->fetchAll(PDO::FETCH_ASSOC);
  }
}

/* formulario como antes */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $ci = trim($_POST['CI'] ?? '');
  $idMascota = trim($_POST['IdMascota'] ?? '');
  $idServicio = trim($_POST['IdServicio'] ?? '');
  $fecha = trim($_POST['Fecha'] ?? '');
  $hora = trim($_POST['Hora'] ?? '');
  $estado = trim($_POST['Estado'] ?? '');

  $sqlCheck = $conexion->prepare("SELECT 1 FROM CLIENTE WHERE CI = :ci");
  $sqlCheck->execute([':ci' => $ci]);

  /* la mascota tiene que ser del cliente */
  $sqlCheckMascota = $conexion->prepare("SELECT 1 FROM MASCOTA WHERE IdMascota = :idMascota AND CI = :ci");
  $sqlCheckMascota->execute([':idMascota' => $idMascota, ':ci' => $ci]);
  $mascotaDelCliente = $sqlCheckMascota->fetch();

  /* se valida antes de guardar */
  if (!$sqlCheck->fetch()) {
    $error = "La cédula no corresponde a ningún cliente.";
  } elseif ($idMascota === '') {
    $error = "Elegí la mascota de la cita.";
  } elseif (!$mascotaDelCliente) {
    $error = "La mascota elegida no es de este cliente.";
  } elseif ($idServicio === '') {
    $error = "Elegí el servicio de la cita.";
  } elseif ($fecha === '') {
    $error = "Falta la fecha de la cita.";
  } elseif (strtotime($fecha) === false) {
    $error = "La fecha de la cita no es válida.";
  } elseif ($fecha < date('Y-m-d')) {
    $error = "La fecha no puede ser anterior a hoy.";
  } elseif ($hora === '') {
    $error = "Falta la hora de la cita.";
  } elseif (!in_array($estado, ['Pendiente', 'Confirmada', 'Cancelada'])) {
    $error = "Elegí un estado válido para la cita.";
  } else {
    try {
      $cita = new Cita();
      $resultado = $cita->crearCita((int)$ci, (int)$idServicio, (int)$idMascota, $fecha, $hora, $estado);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: citas.php");
      exit();
    } else {
      $error = "No se pudo guardar la cita. Revisá los datos.";
    }
  }
}

/*  el modal se abre solo si venimos de buscar una cédula (sin importar si encontro o no) */
/* si al guardar hay un error, se muestra el aviso arriba del listado */
$modalAbierto = isset($_GET['ci']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citas - Panel Sienra</title>

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
    $paginaActiva = 'citas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Citas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($citaEliminar) { ?>
          <div class="alert alert-warning" role="alert">
            <form class="d-flex flex-wrap align-items-center gap-2" action="citas.php" method="post">
              <span class="me-auto">
                ¿Eliminar la cita de <strong><?= htmlspecialchars($citaEliminar['MascotaNombre'] ?? '') ?></strong>
                del <?= date('d/m/Y', strtotime($citaEliminar['Fecha'])) ?> a las <?= substr($citaEliminar['Hora'], 0, 5) ?>? Esta acción no se puede deshacer.
              </span>
              <input type="hidden" name="IdCita" value="<?= $citaEliminar['IdCita'] ?>">
              <a class="btn btn-sm admin-btn-cancelar" href="citas.php">Cancelar</a>
              <button type="submit" class="btn btn-sm btn-danger" name="eliminar">Sí, eliminar</button>
            </form>
          </div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de citas</h2>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <form method="get" action="citas.php" class="d-flex gap-2">
                <label class="visually-hidden" for="filtroEstado">Filtrar por estado</label>
                <select class="form-select form-select-sm admin-filtro" id="filtroEstado" name="estado">
                  <option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos los estados</option>
                  <option value="Pendiente" <?= $estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                  <option value="Confirmada" <?= $estado === 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                  <option value="Cancelada" <?= $estado === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
                <button type="submit" class="btn btn-sm admin-btn-filtrar">Filtrar</button>
              </form>
              <a href="citas.php?ci=" class="btn btn-sm admin-btn-agregar" role="button">
                <i class="bi bi-plus-lg"></i> Agregar cita
              </a>
            </div>
          </div>

          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="citas.php" method="get">
            <input class="form-control" type="date" name="fecha" value="<?= htmlspecialchars($fechaFiltro) ?>">
            <button class="btn admin-btn-agregar ms-2" type="submit" title="Filtrar por fecha"><i class="bi bi-search"></i></button>
            <?php if ($fechaFiltro !== ''): ?>
              <a class="btn admin-btn-cancelar ms-2" href="citas.php">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Fecha</th>
                  <th scope="col">Hora</th>
                  <th scope="col">Cliente</th>
                  <th scope="col">Mascota</th>
                  <th scope="col">Servicio</th>
                  <th scope="col">Estado</th>
                  <th scope="col" class="text-end">Acciones</th>
                </tr>
              </thead>


              <!-- listado de citas -->
              <tbody>
                <?php if (empty($citas)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No hay citas registradas.</td>
                  </tr>
                  <?php else: foreach ($citas as $c): ?>
                    <tr>
                      <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['Fecha']))) ?></td>
                      <td><?= htmlspecialchars(substr($c['Hora'], 0, 5)) ?></td>
                      <td><?= htmlspecialchars($c['ClienteNombre'] . ' ' . $c['ClienteApellido']) ?><br><span class="admin-subtexto">CI: <?= htmlspecialchars($c['CI']) ?></span></td>
                      <td><?= htmlspecialchars($c['MascotaNombre']) ?></td>
                      <td><?= htmlspecialchars($c['ServicioNombre']) ?></td>
                      <td>
                        <?php
                        $claseBadge = match ($c['Estado']) {
                          'Confirmada' => 'badge-success',
                          'Pendiente'  => 'badge-info',
                          'Cancelada'  => 'badge-danger',
                          default      => 'badge-secondary',
                        };
                        ?>
                        <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars($c['Estado']) ?></span>
                      </td>
                      <td class="text-end">
                        <a class="btn btn-sm admin-btn-icono" href="editar_cita.php?id=<?= $c['IdCita'] ?>" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                        <a class="btn btn-sm admin-btn-icono admin-btn-eliminar" href="citas.php?eliminar=<?= $c['IdCita'] ?>" aria-label="Eliminar"><i class="bi bi-trash"></i></a>
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

  <!-- agregar cita -->
  <div class="modal fade admin-modal <?= $modalAbierto ? 'show' : '' ?>"
    id="modalCita"
    tabindex="-1"
    aria-labelledby="modalCitaTitulo"
    aria-hidden="<?= $modalAbierto ? 'false' : 'true' ?>"
    style="<?= $modalAbierto ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title" id="modalCitaTitulo">Agregar cita</h2>
          <a href="citas.php" class="btn-close" aria-label="Cerrar"></a>
        </div>

        <?php if (!$clienteEncontrado): ?>

          <!-- pedido de cedula -->
          <form method="get" action="citas.php">
            <div class="modal-body">
              <label class="form-label" for="ci">Cédula del cliente</label>
              <input class="form-control" type="text" id="ci" name="ci" placeholder="Ej: 45678901"
                value="<?= htmlspecialchars($_GET['ci'] ?? '') ?>" required>
              <?php if ($errorCedula): ?>
                <div class="form-text text-danger"><?= htmlspecialchars($errorCedula) ?></div>
              <?php endif; ?>
            </div>
            <div class="modal-footer">
              <a href="citas.php" class="btn admin-btn-cancelar">Cancelar</a>
              <button type="submit" class="btn admin-btn-agregar">Buscar</button>
            </div>
          </form>

        <?php else: ?>

          <!-- datos de la cita -->
          <form method="post" action="citas.php">
            <div class="modal-body">
              <input type="hidden" name="CI" value="<?= htmlspecialchars($clienteEncontrado['CI']) ?>">

              <div class="mb-3">
                <span class="text-muted">Cliente:</span>
                <strong><?= htmlspecialchars($clienteEncontrado['Nombre'] . ' ' . $clienteEncontrado['Apellido']) ?></strong>
              </div>

              <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>

              <div class="row g-3">
                <div class="col-sm-6">
                  <label class="form-label" for="IdMascota">Mascota</label>
                  <select class="form-select" id="IdMascota" name="IdMascota" required>
                    <option value="" selected disabled>Elegir mascota</option>
                    <?php foreach ($mascotasCliente as $m): ?>
                      <option value="<?= $m['IdMascota'] ?>"><?= htmlspecialchars($m['Nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (empty($mascotasCliente)): ?>
                    <div class="form-text text-warning">Este cliente no tiene mascotas registradas.</div>
                  <?php endif; ?>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="IdServicio">Servicio</label>
                  <select class="form-select" id="IdServicio" name="IdServicio" required>
                    <option value="" selected disabled>Elegir servicio</option>
                    <?php foreach ($servicios as $s): ?>
                      <option value="<?= $s['IdServicio'] ?>"><?= htmlspecialchars($s['Nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="Fecha">Fecha</label>
                  <input class="form-control" type="date" id="Fecha" name="Fecha" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="Hora">Hora</label>
                  <input class="form-control" type="time" id="Hora" name="Hora" required>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="Estado">Estado</label>
                  <select class="form-select" id="Estado" name="Estado" required>
                    <option value="Pendiente" selected>Pendiente</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Cancelada">Cancelada</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <a href="citas.php" class="btn admin-btn-cancelar">Cancelar</a>
              <button type="submit" name="guardar" class="btn admin-btn-agregar">Guardar</button>
            </div>
          </form>

        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($modalAbierto): ?>
    <div class="modal-backdrop fade show"></div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>