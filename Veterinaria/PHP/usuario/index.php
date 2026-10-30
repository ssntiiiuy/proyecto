<?php
/* $rolesPermitidos = ['Cliente'];
require_once __DIR__ . '/../../includes/verificar_sesion.php'; // hay que agregarlo pero todavia no anda */
session_start();

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';
require_once __DIR__ . '/../clases/Cita.php';
require_once __DIR__ . '/../clases/Servicio.php';

$nombreUsuario = "Usuario";
$errorAgenda = null;
$avisoAgenda = isset($_GET['agendada']);

try {
  $conexion = (new Conexion())->establecerConexion();

  $sql = "SELECT usuario.CI, usuario.Nombre FROM USUARIO INNER JOIN HACE ON usuario.CI = hace.CI WHERE hace.Correo = :correo";
  $stmt = $conexion->prepare($sql);
  $stmt->execute([':correo' => $_SESSION['correo']]);
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$usuario) {
    header('Location: ../LoginHTML.php');
    exit();
  }

  $nombreUsuario = $usuario['Nombre'];
  $ci = (int) $usuario['CI'];

  $mascotas = Mascota::listadoMascotasPorCliente($ci);
  $servicios = Servicio::listadoServicios()['servicios'];

  $sqlTurno = "SELECT c.Fecha, c.Hora, c.Estado, s.Nombre AS NombreServicio, m.Nombre AS NombreMascota
             FROM CITA c
             INNER JOIN SERVICIO s ON c.IdServicio = s.IdServicio
             INNER JOIN MASCOTA m ON c.IdMascota = m.IdMascota
             WHERE c.CI = :ci AND c.Fecha >= CURDATE()
             ORDER BY c.Fecha ASC, c.Hora ASC LIMIT 1";
  $stmtTurno = $conexion->prepare($sqlTurno);
  $stmtTurno->execute([':ci' => $ci]);
  $proximoTurno = $stmtTurno->fetch(PDO::FETCH_ASSOC);

  $sqlCarnet = "SELECT cv.TipoVacunacion, cv.FechaProximaVacunacion, m.Nombre AS NombreMascota
              FROM CARNET_VACUNACION cv
              INNER JOIN MASCOTA m ON cv.IdMascota = m.IdMascota
              WHERE m.CI = :ci
              ORDER BY m.Nombre, cv.FechaProximaVacunacion";
  $stmtCarnet = $conexion->prepare($sqlCarnet);
  $stmtCarnet->execute([':ci' => $ci]);
  $carnet = $stmtCarnet->fetchAll(PDO::FETCH_ASSOC);

  $proximaVacuna = null;
  $limiteAlerta = (new DateTime())->modify('+1 month');

  foreach ($carnet as $v) {
    if (!empty($v['FechaProximaVacunacion'])) {
      $fechaVenc = new DateTime($v['FechaProximaVacunacion']);
      if ($fechaVenc <= $limiteAlerta) {
        if (!$proximaVacuna || $fechaVenc < new DateTime($proximaVacuna['FechaProximaVacunacion'])) {
          $proximaVacuna = $v;
        }
      }
    }
  }
} catch (PDOException $e) {
  echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red; margin:10px;'>";
  echo "<b>Error SQL en index.php:</b> " . $e->getMessage();
  echo "</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($ci)) {
  $idMascota = (int)($_POST['IdMascota'] ?? 0);
  $idServicio = (int)($_POST['IdServicio'] ?? 0);
  $fecha = trim($_POST['Fecha'] ?? '');
  $hora = trim($_POST['Hora'] ?? '');
  $fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);

  if (!$idMascota || !Cita::mascotaPerteneceACliente($idMascota, $ci)) {
    $errorAgenda = 'Elegí una de tus mascotas.';
  } elseif (!in_array($idServicio, array_map('intval', array_column($servicios, 'IdServicio')), true)) {
    $errorAgenda = 'Elegí un servicio disponible.';
  } elseif (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha || $fecha < date('Y-m-d')) {
    $errorAgenda = 'La fecha debe ser hoy o posterior.';
  } elseif (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
    $errorAgenda = 'Ingresá una hora válida.';
  } else {
    $cita = new Cita();
    if ($cita->crearCita($ci, $idServicio, $idMascota, $fecha, $hora, 'Pendiente')) {
      header('Location: index.php?agendada=1');
      exit();
    }
    $errorAgenda = 'No se pudo solicitar el turno. Intentá nuevamente.';
  }
}

// variables que necesita header.php
$paginaActiva = 'citas';
$mostrarCarrito = true;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>

  <?php require __DIR__ . '/includes/header.php'; ?>

  <main class="dashboard">
    <div class="container-lg py-4">

      <div class="greeting-card">
        <h1>Hola, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
      </div>

      <?php if ($proximaVacuna): ?>
        <div class="alert-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle"></i>
            <span><strong>Vacuna <?= htmlspecialchars($proximaVacuna['TipoVacunacion']) ?> de <?= htmlspecialchars($proximaVacuna['NombreMascota']) ?></strong>
              vence <?= date('d/m/Y', strtotime($proximaVacuna['FechaProximaVacunacion'])) ?></span>
          </div>
          <button class="btn btn-outline-light btn-sm px-3" type="button" data-bs-toggle="modal" data-bs-target="#modalSolicitarCita">Pedir cita</button>
        </div>
      <?php endif; ?>

      <div class="row g-3 mt-1">

        <?php if ($avisoAgenda) { ?>
          <div class="col-12"><div class="alert alert-success mb-0">Tu solicitud de turno quedó pendiente de confirmación.</div></div>
        <?php } ?>
        <?php if ($errorAgenda) { ?>
          <div class="col-12"><div class="alert alert-danger mb-0"><?= htmlspecialchars($errorAgenda) ?></div></div>
        <?php } ?>

        <div class="col-lg-6 d-flex flex-column gap-3">

          <div class="panel-card">
            <div class="panel-header">
              <h2>Mis mascotas</h2>
              <span class="badge badge-info"><?= count($mascotas) ?> total</span>
            </div>
            <?php foreach ($mascotas as $i => $m): ?>
              <?php if ($i > 0): ?>
                <hr class="divider"><?php endif; ?>
              <div class="pet-row">
                <div>
                  <p class="pet-name"><?= htmlspecialchars($m['Nombre']) ?></p>
                  <p class="pet-meta"><?= htmlspecialchars($m['Raza']) ?> - <?= htmlspecialchars($m['Especie']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="panel-card">
            <div class="panel-header">
              <h2>Mis turnos</h2>
            </div>
            <?php if ($proximoTurno): ?>
              <div class="pet-row">
                <div>
                  <p class="pet-name"><?= htmlspecialchars($proximoTurno['NombreMascota']) ?></p>
                  <p class="pet-meta"><?= htmlspecialchars($proximoTurno['NombreServicio']) ?> -
                    <?= date('d/m/Y', strtotime($proximoTurno['Fecha'])) ?> - <?= substr($proximoTurno['Hora'], 0, 5) ?></p>
                </div>
                <span class="badge badge-success"><?= htmlspecialchars($proximoTurno['Estado']) ?></span>
              </div>
            <?php else: ?>
              <p class="pet-meta">No tenés turnos próximos.</p>
            <?php endif; ?>
          </div>

        </div>

        <div class="col-lg-6 d-flex flex-column gap-3">

          <div class="panel-card flex-grow-1 d-flex flex-column">
            <div class="panel-header">
              <h2>Carnet de vacunas</h2>
            </div>

            <?php
            $mascotaActual = null;
            if (empty($carnet)) {
              echo '<p class="pet-meta text-muted p-3">No hay registros de vacunación.</p>';
            } else {
              foreach ($carnet as $vacuna) {
                if ($mascotaActual !== $vacuna['NombreMascota']) {
                  $mascotaActual = $vacuna['NombreMascota'];
                  echo '<p class="group-label mt-3">' . htmlspecialchars($mascotaActual) . '</p>';
                } else {
                  echo '<hr class="divider">';
                }

                $fechaVenc = $vacuna['FechaProximaVacunacion'];
                $fechaFormateada = $fechaVenc ? date('d/m/Y', strtotime($fechaVenc)) : 'Sin fecha';

                $vencida = $fechaVenc && $fechaVenc < date('Y-m-d');
                $claseBadge = $vencida ? 'badge badge-danger' : 'badge badge-success';
            ?>
                <div class="pet-row">
                  <span class="pet-meta"><?= htmlspecialchars($vacuna['TipoVacunacion']) ?></span>
                  <span class="<?= $claseBadge ?>">Vence: <?= $fechaFormateada ?></span>
                </div>
            <?php
              }
            }
            ?>

            <button class="btn btn-cta mt-auto" type="button" data-bs-toggle="modal" data-bs-target="#modalSolicitarCita">Solicitar nuevo turno</button>
          </div>

        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="modalSolicitarCita" tabindex="-1" aria-labelledby="modalSolicitarCitaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="post" action="index.php">
          <div class="modal-header">
            <h2 class="modal-title fs-5" id="modalSolicitarCitaTitulo">Solicitar un turno</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <?php if (empty($mascotas)) { ?>
              <p>Primero necesitás registrar una mascota para solicitar un turno.</p>
            <?php } elseif (empty($servicios)) { ?>
              <p>No hay servicios disponibles para seleccionar.</p>
            <?php } else { ?>
              <div class="mb-3">
                <label class="form-label" for="IdMascota">Mascota</label>
                <select class="form-select" id="IdMascota" name="IdMascota" required>
                  <option value="" selected disabled>Elegí una mascota</option>
                  <?php foreach ($mascotas as $mascota) { ?>
                    <option value="<?= (int)$mascota['IdMascota'] ?>"><?= htmlspecialchars($mascota['Nombre']) ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="IdServicio">Servicio</label>
                <select class="form-select" id="IdServicio" name="IdServicio" required>
                  <option value="" selected disabled>Elegí un servicio</option>
                  <?php foreach ($servicios as $servicio) { ?>
                    <option value="<?= (int)$servicio['IdServicio'] ?>"><?= htmlspecialchars($servicio['Nombre']) ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="row g-3">
                <div class="col-sm-6">
                  <label class="form-label" for="Fecha">Fecha</label>
                  <input class="form-control" type="date" id="Fecha" name="Fecha" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="Hora">Hora</label>
                  <input class="form-control" type="time" id="Hora" name="Hora" required>
                </div>
              </div>
              <p class="form-text mb-0 mt-3">La solicitud quedará pendiente hasta que la clínica la confirme.</p>
            <?php } ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <?php if (!empty($mascotas) && !empty($servicios)) { ?>
              <button class="btn btn-cta" type="submit">Enviar solicitud</button>
            <?php } ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- chatbot -->
  <script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/10/27/11/20261027111945-MM3FDTVD.js" defer></script>

</body>

</html>
