<?php
require_once __DIR__ . '/../../clases/Conexion.php';
require_once __DIR__ . '/../../clases/Cita.php';
require_once __DIR__ . '/../../clases/Producto.php';

$estadoFiltro = $_GET['estado'] ?? '';
$datosCitas = Cita::listadoCita($estadoFiltro !== '' ? $estadoFiltro : null);
$citas = $datosCitas['citas'];

/* limitar a las próximas 5 citas en el dashboard */
$citasProximas = array_slice($citas, 0, 5);

$citasHoy = Cita::contarCitasHoy();
$stockBajo = Producto::contarStockBajo();
$porVencer = Producto::contarPorVencer();

$conexion = (new Conexion())->establecerConexion();
$clientesRegistrados = (int)$conexion->query("SELECT COUNT(*) FROM CLIENTE")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/../../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
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

        <!-- lleva a citas filtrado por "hoy" -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <a href="../citas.php?filtro=hoy" class="admin-stat admin-stat-info text-decoration-none">
              <div>
                <p class="admin-stat-label">Citas de hoy</p>
                <p class="admin-stat-valor"><?= $citasHoy ?></p>
              </div>
              <i class="bi bi-calendar-check"></i>
            </a>
          </div>

          <!-- modulo con href para stock bajo y productos por vencer -->
          <div class="col-sm-6 col-xl-3">
            <a href="../productos.php?filtro=stock_bajo" class="admin-stat admin-stat-danger text-decoration-none">
              <div>
                <p class="admin-stat-label">PRODUCTOS CON STOCK BAJO</p>
                <p class="admin-stat-valor"><?= Producto::contarStockBajo() ?></p>
              </div>
              <i class="bi bi-box-seam"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="../productos.php?filtro=por_vencer" class="admin-stat admin-stat-warning text-decoration-none">
              <div>
                <p class="admin-stat-label">PRODUCTOS POR VENCER</p>
                <p class="admin-stat-valor"><?= Producto::contarPorVencer() ?></p>
              </div>
              <i class="bi bi-hourglass-split"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="../usuarios.php" class="admin-stat admin-stat-success text-decoration-none">
              <div>
                <p class="admin-stat-label">Clientes registrados</p>
                <p class="admin-stat-valor"><?= $clientesRegistrados ?></p>
              </div>
              <i class="bi bi-people"></i>
            </a>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h2>Próximas citas</h2>
            <a class="admin-card-link" href="citas.php">Ver todas</a>
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
              <!-- TODO BD: próximas citas con cliente, mascota y servicio -->
              <tbody>
                <?php if (empty($citas)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No hay citas registradas.</td>
                  </tr>
                  <?php else: foreach ($citas as $c): ?>
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
                      <td class="text-end">
                        <button class="btn btn-sm admin-btn-icono" type="button" aria-label="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm admin-btn-icono admin-btn-eliminar" type="button" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
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