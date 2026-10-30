<?php
$rolesPermitidos = ['Cajero'];
require_once __DIR__ . '/../../includes/verificar_sesion.php';

require_once __DIR__ . '/../../clases/Conexion.php';
require_once __DIR__ . '/../../clases/Factura.php';
require_once __DIR__ . '/../../clases/Producto.php';

$facturasHoy = Factura::contarFacturasHoy();
$ventasHoy = Factura::totalVentasHoy();
$stockBajo = Producto::contarStockBajo();
$ultimasFacturas = array_slice(Factura::listadoFactura()['facturas'], 0, 5);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cajero - Panel Sienra</title>

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
          <div class="col-sm-6 col-xl-3">
            <a href="../facturas.php" class="admin-stat admin-stat-info text-decoration-none">
              <div>
                <p class="admin-stat-label">Facturas de hoy</p>
                <p class="admin-stat-valor"><?= $facturasHoy ?></p>
              </div>
              <i class="bi bi-receipt"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="../facturas.php" class="admin-stat admin-stat-success text-decoration-none">
              <div>
                <p class="admin-stat-label">Ventas de hoy</p>
                <p class="admin-stat-valor">$ <?= number_format($ventasHoy, 0, ',', '.') ?></p>
              </div>
              <i class="bi bi-cash-coin"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <!-- sin link: todavia no hay una vista de productos para el rol cajero -->
            <div class="admin-stat admin-stat-danger">
              <div>
                <p class="admin-stat-label">Productos con stock bajo</p>
                <p class="admin-stat-valor"><?= $stockBajo ?></p>
              </div>
              <i class="bi bi-box-seam"></i>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="../caja.php" class="admin-stat admin-stat-warning text-decoration-none">
              <div>
                <p class="admin-stat-label">&nbsp;</p>
                <p class="admin-stat-valor" style="font-size:1.1rem">+ Nueva venta</p>
              </div>
              <i class="bi bi-plus-circle"></i>
            </a>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h2>Últimas facturas</h2>
            <a class="admin-card-link" href="../facturas.php">Ver todas</a>
          </div>
          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">N.º</th>
                  <th scope="col">Fecha</th>
                  <th scope="col">Cliente</th>
                  <th scope="col" class="text-end">Total</th>
                  <th scope="col" class="text-end">Detalle</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($ultimasFacturas)): ?>
                  <tr>
                    <td colspan="5" class="text-center">No hay facturas registradas.</td>
                  </tr>
                  <?php else: foreach ($ultimasFacturas as $f): ?>
                    <tr>
                      <td><?= str_pad((string)$f['IdFactura'], 4, '0', STR_PAD_LEFT) ?></td>
                      <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['Fecha']))) ?></td>
                      <td>
                        <?php if ($f['ClienteNombre']) { ?>
                          <?= htmlspecialchars($f['ClienteNombre'] . ' ' . $f['ClienteApellido']) ?>
                        <?php } else { ?>
                          <span class="admin-sin-dato">Sin cliente</span>
                        <?php } ?>
                      </td>
                      <td class="text-end">$ <?= number_format((float)$f['Total'], 2, ',', '.') ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm admin-btn-icono" href="../factura.php?IdFactura=<?= $f['IdFactura'] ?>"><i class="bi bi-eye"></i> Ver</a>
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
