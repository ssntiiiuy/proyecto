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

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'dashboard';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Dashboard';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <!-- TODO BD: contar citas de hoy, stock bajo (Stock < StockMinimo), por vencer y clientes -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="admin-stat admin-stat-info">
              <div>
                <p class="admin-stat-label">Citas de hoy</p>
                <p class="admin-stat-valor">3</p>
              </div>
              <i class="bi bi-calendar-check"></i>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-stat admin-stat-danger">
              <div>
                <p class="admin-stat-label">Productos con stock bajo</p>
                <p class="admin-stat-valor">2</p>
              </div>
              <i class="bi bi-box-seam"></i>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-stat admin-stat-warning">
              <div>
                <p class="admin-stat-label">Productos por vencer</p>
                <p class="admin-stat-valor">1</p>
              </div>
              <i class="bi bi-hourglass-split"></i>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-stat admin-stat-success">
              <div>
                <p class="admin-stat-label">Clientes registrados</p>
                <p class="admin-stat-valor">48</p>
              </div>
              <i class="bi bi-people"></i>
            </div>
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
                <tr>
                  <td>08/10/2026</td>
                  <td>09:30</td>
                  <td>Jorge Pérez</td>
                  <td>Rex</td>
                  <td>Consulta general</td>
                  <td><span class="badge badge-success">Confirmada</span></td>
                </tr>
                <tr>
                  <td>08/10/2026</td>
                  <td>11:00</td>
                  <td>María González</td>
                  <td>Luna</td>
                  <td>Vacuna antirrábica</td>
                  <td><span class="badge badge-success">Confirmada</span></td>
                </tr>
                <tr>
                  <td>08/10/2026</td>
                  <td>15:30</td>
                  <td>Lucía Fernández</td>
                  <td>Milo</td>
                  <td>Baño y corte</td>
                  <td><span class="badge badge-info">Pendiente</span></td>
                </tr>
                <tr>
                  <td>09/10/2026</td>
                  <td>10:00</td>
                  <td>Martín Silva</td>
                  <td>Toby</td>
                  <td>Castración</td>
                  <td><span class="badge badge-danger">Cancelada</span></td>
                </tr>
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
