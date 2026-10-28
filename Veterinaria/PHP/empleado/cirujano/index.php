<?php
require_once __DIR__ . '/../../clases/Conexion.php';
require_once __DIR__ . '/../../clases/Cita.php';

$tipos = ['Cirugia'];

$datosCitas = Cita::listadoCitaPorTipo($tipos);
$citasProximas = array_slice($datosCitas['citas'], 0, 5);

$citasHoy = Cita::contarCitasHoyPorTipo($tipos);
$pendientes = count(Cita::listadoCitaPorTipo($tipos, 'Pendiente')['citas']);
$confirmadas = count(Cita::listadoCitaPorTipo($tipos, 'Confirmada')['citas']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cirujano - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../../CSS/style.css">
  <link rel="stylesheet" href="../../../CSS/admin.css">
  <script src="../../../JS/tema.js"></script>
</head>

<body>

  <div class="admin-wrapper" data-bs-theme="light">
    <?php
    $paginaActiva = 'dashboard';
    include __DIR__ . '/../includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Dashboard';
      include __DIR__ . '/../includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-4">
            <a href="../citas.php?filtro=hoy" class="admin-stat admin-stat-info text-decoration-none">
              <div>
                <p class="admin-stat-label">Cirugías de hoy</p>
                <p class="admin-stat-valor"><?= $citasHoy ?></p>
              </div>
              <i class="bi bi-calendar-check"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-4">
            <a href="../citas.php?estado=Pendiente" class="admin-stat admin-stat-warning text-decoration-none">
              <div>
                <p class="admin-stat-label">Pendientes</p>
                <p class="admin-stat-valor"><?= $pendientes ?></p>
              </div>
              <i class="bi bi-hourglass-split"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-4">
            <a href="../citas.php?estado=Confirmada" class="admin-stat admin-stat-success text-decoration-none">
              <div>
                <p class="admin-stat-label">Confirmadas</p>
                <p class="admin-stat-valor"><?= $confirmadas ?></p>
              </div>
              <i class="bi bi-check-circle"></i>
            </a>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h2>Mi próxima agenda</h2>
            <a class="admin-card-link" href="../citas.php">Ver toda mi agenda</a>
          </div>
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
                </tr>
              </thead>
              <tbody>
                <?php if (empty($citasProximas)): ?>
                  <tr>
                    <td colspan="6" class="text-center">No tenés citas registradas.</td>
                  </tr>
                  <?php else: foreach ($citasProximas as $c): ?>
                    <tr>
                      <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['Fecha']))) ?></td>
                      <td><?= htmlspecialchars(substr($c['Hora'], 0, 5)) ?></td>
                      <td><?= htmlspecialchars($c['ClienteNombre'] . ' ' . $c['ClienteApellido']) ?></td>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
