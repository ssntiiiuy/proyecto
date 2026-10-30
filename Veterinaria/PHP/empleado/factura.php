<?php
$rolesPermitidos = ['Cajero'];
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/Conexion.php';

// Obtener el ID de la factura desde la URL
$idFactura = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['IdFactura']) ? (int) $_GET['IdFactura'] : 0);

$conexion = (new Conexion())->establecerConexion();

// 1. Buscar los datos generales de la factura y del cliente asociado mediante la tabla compra
$sqlFactura = $conexion->prepare("
    SELECT f.*, u.Nombre as ClienteNombre, u.Apellido as ClienteApellido, u.CI as ClienteCI
    FROM factura f
    LEFT JOIN compra co ON f.IdFactura = co.IdFactura
    LEFT JOIN cliente c ON co.CI = c.CI
    LEFT JOIN usuario u ON c.CI = u.CI
    WHERE f.IdFactura = :id
    LIMIT 1
");
$sqlFactura->execute([':id' => $idFactura]);
$factura = $sqlFactura->fetch(PDO::FETCH_ASSOC);

// Si no existe la factura, redirigir al listado
if (!$factura) {
    header("Location: facturas.php?msg=noexiste");
    exit();
}

// 2. Buscar las líneas y productos asociados a esta factura
try {
    $sqlLineas = $conexion->prepare("
        SELECT lf.*, p.Nombre as ProductoNombre, p.Precio as ProductoPrecio
        FROM linea_factura lf
        JOIN compra co ON lf.IdFactura = co.IdFactura AND lf.IdLineaFactura = co.IdLineaFactura
        JOIN producto p ON co.IdProducto = p.IdProducto
        WHERE lf.IdFactura = :id
    ");
    $sqlLineas->execute([':id' => $idFactura]);
    $lineas = $sqlLineas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si hay un error en la consulta SQL, lo mostramos en pantalla para depurar
    echo "Error en SQL de líneas: " . $e->getMessage();
    exit();
}

$subtotalGeneral = 0;
$ivaGeneral = 0;
?>
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
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
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

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <a class="btn btn-sm admin-btn-cancelar" href="facturas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
        </div>

        <div class="admin-card mb-4">
          <div class="admin-card-header">
            <h2>Factura N.º <?= str_pad($factura['IdFactura'], 4, '0', STR_PAD_LEFT) ?></h2>
          </div>
          <div class="row g-3 admin-factura-datos">
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Emitida por</p>
              <p class="admin-dato-valor"><?= htmlspecialchars($factura['NombreVeterinaria'] ?? 'Clínica Veterinaria Sienra') ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">RUT</p>
              <p class="admin-dato-valor"><?= htmlspecialchars($factura['Rut'] ?? '—') ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Cliente</p>
              <p class="admin-dato-valor">
                <?= htmlspecialchars(($factura['ClienteNombre'] ?? 'Cliente') . ' ' . ($factura['ClienteApellido'] ?? '')) ?>
                <span class="admin-sin-dato">(<?= htmlspecialchars($factura['ClienteCI'] ?? 'Sin CI') ?>)</span>
              </p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Fecha</p>
              <p class="admin-dato-valor"><?= isset($factura['Fecha']) && $factura['Fecha'] ? date('d/m/Y', strtotime($factura['Fecha'])) : '—' ?></p>
            </div>
            <div class="col-sm-6 col-xl-4">
              <p class="admin-dato-label">Vencimiento</p>
              <p class="admin-dato-valor"><?= isset($factura['FechaVencimiento']) && $factura['FechaVencimiento'] ? date('d/m/Y', strtotime($factura['FechaVencimiento'])) : '—' ?></p>
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
                    <td colspan="6" class="text-center">No hay líneas registradas para esta factura.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($lineas as $index => $l):
                        $cantidad = isset($l['Cantidad']) ? $l['Cantidad'] : 1;
                        $precioUnitario = isset($l['ProductoPrecio']) ? $l['ProductoPrecio'] : 0;
                        $subtotalLinea = $cantidad * $precioUnitario;
                        $porcentajeIva = 22; // IVA estándar
                        $ivaLinea = $subtotalLinea * ($porcentajeIva / 100);

                        $subtotalGeneral += $subtotalLinea;
                        $ivaGeneral += $ivaLinea;
                  ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td><?= htmlspecialchars($l['ProductoNombre'] ?? 'Artículo') ?></td>
                      <td class="text-end"><?= $cantidad ?></td>
                      <td class="text-end">$ <?= number_format($precioUnitario, 2, ',', '.') ?></td>
                      <td class="text-end"><?= $porcentajeIva ?> %</td>
                      <td class="text-end">$ <?= number_format($subtotalLinea, 2, ',', '.') ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php
            $totalGeneral = $subtotalGeneral + $ivaGeneral;
          ?>
          <dl class="admin-factura-totales">
            <div>
              <dt>Subtotal</dt>
              <dd>$ <?= number_format($subtotalGeneral, 2, ',', '.') ?></dd>
            </div>
            <div>
              <dt>IVA (22%)</dt>
              <dd>$ <?= number_format($ivaGeneral, 2, ',', '.') ?></dd>
            </div>
            <div class="admin-factura-total">
              <dt>Total</dt>
              <dd>$ <?= number_format($totalGeneral, 2, ',', '.') ?></dd>
            </div>
          </dl>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>