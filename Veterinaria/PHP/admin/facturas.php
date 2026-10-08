<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facturas - Panel Sienra</title>

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
      $tituloPagina = 'Facturas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header">
            <h2>Listado de facturas</h2>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">N.º</th>
                  <th scope="col">Fecha</th>
                  <th scope="col">Vencimiento</th>
                  <th scope="col">Cliente</th>
                  <th scope="col">RUT</th>
                  <th scope="col" class="text-end">Total</th>
                  <th scope="col" class="text-end">Detalle</th>
                </tr>
              </thead>
              <!-- TODO BD: FACTURA + cliente por COMPRA; el total sale de sumar las líneas (Cantidad x PRODUCTO.Precio + IVA) -->
              <tbody>
                <tr>
                  <td>0004</td>
                  <td>08/10/2026</td>
                  <td>07/11/2026</td>
                  <td>Jorge Pérez<span class="admin-subtexto">4.567.890-1</span></td>
                  <td>217654320018</td>
                  <td class="text-end">$ 4.875,12</td>
                  <td class="text-end">
                    <a class="btn btn-sm admin-btn-icono" href="factura.php?IdFactura=4"><i class="bi bi-eye"></i> Ver</a>
                  </td>
                </tr>
                <tr>
                  <td>0003</td>
                  <td>05/10/2026</td>
                  <td>04/11/2026</td>
                  <td>María González<span class="admin-subtexto">3.876.543-2</span></td>
                  <td>217654320018</td>
                  <td class="text-end">$ 3.046,34</td>
                  <td class="text-end">
                    <a class="btn btn-sm admin-btn-icono" href="factura.php?IdFactura=3"><i class="bi bi-eye"></i> Ver</a>
                  </td>
                </tr>
                <tr>
                  <td>0002</td>
                  <td>28/09/2026</td>
                  <td>28/10/2026</td>
                  <td>Lucía Fernández<span class="admin-subtexto">5.123.456-7</span></td>
                  <td>217654320018</td>
                  <td class="text-end">$ 1.338,34</td>
                  <td class="text-end">
                    <a class="btn btn-sm admin-btn-icono" href="factura.php?IdFactura=2"><i class="bi bi-eye"></i> Ver</a>
                  </td>
                </tr>
                <tr>
                  <td>0001</td>
                  <td>20/09/2026</td>
                  <td>20/10/2026</td>
                  <td>Martín Silva<span class="admin-subtexto">4.234.567-8</span></td>
                  <td>217654320018</td>
                  <td class="text-end">$ 2.193,56</td>
                  <td class="text-end">
                    <a class="btn btn-sm admin-btn-icono" href="factura.php?IdFactura=1"><i class="bi bi-eye"></i> Ver</a>
                  </td>
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
