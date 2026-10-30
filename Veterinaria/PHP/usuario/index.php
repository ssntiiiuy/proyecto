<?php
/* $rolesPermitidos = ['Cliente'];
require_once __DIR__ . '/../../includes/verificar_sesion.php'; // hay que agregarlo pero todavia no anda */
session_start();

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

$nombreUsuario = "Usuario";

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
          <button class="btn btn-outline-light btn-sm px-3" type="button">Pedir cita</button>
        </div>
      <?php endif; ?>

      <div class="row g-3 mt-1">

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

            <button class="btn btn-cta mt-auto" type="button">Solicitar nuevo turno</button>
          </div>

        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- chatbot -->
  <script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/10/27/11/20261027111945-MM3FDTVD.js" defer></script>

</body>

</html>