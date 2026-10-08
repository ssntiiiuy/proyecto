<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalle de factura - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
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

        <!-- TODO BD: buscar la factura por $_GET['IdFactura']; por ahora siempre se ve la 0004 -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <a class="btn btn-sm admin-btn-cancelar" href="facturas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
        </div>

        <div class="admin-card mb-4">
          <div class="admin-card-header">
            <h2>Factura N.º 0004</h2>
          </div>
          <div class="row g-3 admin-factura-datos">
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Emitida por</p>
              <p class="admin-dato-valor">Clínica Veterinaria Sienra</p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">RUT</p>
              <p class="admin-dato-valor">217654320018</p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Cliente</p>
              <p class="admin-dato-valor">Jorge Pérez <span class="admin-sin-dato">(4.567.890-1)</span></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Fecha</p>
              <p class="admin-dato-valor">08/10/2026</p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Vencimiento</p>
              <p class="admin-dato-valor">07/11/2026</p>
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
              <!-- TODO BD: LINEA_FACTURA + producto por ARTICULO_FACTURA, precio de PRODUCTO.Precio -->
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Comida para perros 3kg</td>
                  <td class="text-end">2</td>
                  <td class="text-end">$ 1.299,00</td>
                  <td class="text-end">22 %</td>
                  <td class="text-end">$ 2.598,00</td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Plato para perros</td>
                  <td class="text-end">1</td>
                  <td class="text-end">$ 499,00</td>
                  <td class="text-end">22 %</td>
                  <td class="text-end">$ 499,00</td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Cucha para perros</td>
                  <td class="text-end">1</td>
                  <td class="text-end">$ 899,00</td>
                  <td class="text-end">22 %</td>
                  <td class="text-end">$ 899,00</td>
                </tr>
              </tbody>
            </table>
          </div>

          <dl class="admin-factura-totales">
            <div>
              <dt>Subtotal</dt>
              <dd>$ 3.996,00</dd>
            </div>
            <div>
              <dt>IVA (22 %)</dt>
              <dd>$ 879,12</dd>
            </div>
            <div class="admin-factura-total">
              <dt>Total</dt>
              <dd>$ 4.875,12</dd>
            </div>
          </dl>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
