<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Factura.php';

$rolesPermitidos = ['Cajero'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

$idFactura = (int)($_GET['IdFactura'] ?? 0);
$factura = Factura::buscarPorId($idFactura);

if (!$factura) {
  header('Location: facturas.php?msg=noexiste');
  exit();
}

$lineas = Factura::listadoLineas($idFactura);

$subtotal = 0.0;
$iva = 0.0;
foreach ($lineas as $l) {
  $subtotal += (float)$l['Subtotal'];
  $iva += (float)$l['SubtotalConIva'] - (float)$l['Subtotal'];
}
$total = $subtotal + $iva;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Factura N.º <?= str_pad((string)$factura['IdFactura'], 4, '0', STR_PAD_LEFT) ?> - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>

  <div class="admin-wrapper" data-bs-theme="light">
    <?php
    $paginaActiva = 'facturas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Detalle de factura';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <a class="btn btn-sm admin-btn-cancelar" href="facturas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
        </div>

        <div class="admin-card mb-4">
          <div class="admin-card-header">
            <h2>Factura N.º <?= str_pad((string)$factura['IdFactura'], 4, '0', STR_PAD_LEFT) ?></h2>
          </div>
          <div class="row g-3 admin-factura-datos">
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Emitida por</p>
              <p class="admin-dato-valor"><?= htmlspecialchars($factura['NombreVeterinaria']) ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">RUT</p>
              <p class="admin-dato-valor"><?= htmlspecialchars($factura['Rut']) ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Cliente</p>
              <p class="admin-dato-valor">
                <?php if ($factura['ClienteNombre']) { ?>
                  <?= htmlspecialchars($factura['ClienteNombre'] . ' ' . $factura['ClienteApellido']) ?>
                  <span class="admin-sin-dato">(CI <?= htmlspecialchars($factura['ClienteCI']) ?>)</span>
                <?php } else { ?>
                  <span class="admin-sin-dato">Sin cliente</span>
                <?php } ?>
              </p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Fecha</p>
              <p class="admin-dato-valor"><?= htmlspecialchars(date('d/m/Y', strtotime($factura['Fecha']))) ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Vencimiento</p>
              <p class="admin-dato-valor"><?= htmlspecialchars(date('d/m/Y', strtotime($factura['FechaVencimiento']))) ?></p>
            </div>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-card-header">
            <h2>Líneas</h2>
          </div>
          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">N.º línea</th>
                  <th scope="col">Producto</th>
                  <th scope="col" class="text-end">Cantidad</th>
                  <th scope="col" class="text-end">Precio unitario</th>
                  <th scope="col" class="text-end">IVA</th>
                  <th scope="col" class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($lineas)): ?>
                  <tr>
                    <td colspan="6" class="text-center">Esta factura no tiene líneas.</td>
                  </tr>
                  <?php else: foreach ($lineas as $l): ?>
                    <tr>
                      <td><?= $l['IdLineaFactura'] ?></td>
                      <td><?= htmlspecialchars($l['ProductoNombre']) ?></td>
                      <td class="text-end"><?= (int)$l['Cantidad'] ?></td>
                      <td class="text-end">$ <?= number_format((float)$l['PrecioUnitario'], 2, ',', '.') ?></td>
                      <td class="text-end"><?= number_format((float)$l['IVA'], 0) ?> %</td>
                      <td class="text-end">$ <?= number_format((float)$l['SubtotalConIva'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach;
                endif; ?>
              </tbody>
            </table>
          </div>

          <dl class="admin-factura-totales">
            <div>
              <dt>Subtotal</dt>
              <dd>$ <?= number_format($subtotal, 2, ',', '.') ?></dd>
            </div>
            <div>
              <dt>IVA</dt>
              <dd>$ <?= number_format($iva, 2, ',', '.') ?></dd>
            </div>
            <div class="admin-factura-total">
              <dt>Total</dt>
              <dd>$ <?= number_format($total, 2, ',', '.') ?></dd>
            </div>
          </dl>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
