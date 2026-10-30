<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Factura.php';

$rolesPermitidos = ['Cajero'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

$mensajes = [
  'creada' => ['success', 'La factura se emitió correctamente.'],
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;

$busqueda = trim($_GET['buscar'] ?? '');
$facturas = Factura::listadoFactura($busqueda)['facturas'];
?>

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
      $tituloPagina = 'Facturas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($mensaje) { ?>
          <div class="alert alert-<?= $mensaje[0] ?>" role="alert"><?= htmlspecialchars($mensaje[1]) ?></div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de facturas</h2>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <form class="d-flex gap-2" action="facturas.php" method="get">
                <label class="visually-hidden" for="buscar">Buscar</label>
                <input class="form-control form-control-sm admin-filtro" type="text" id="buscar" name="buscar"
                  placeholder="N.º, cliente o RUT" value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit" class="btn btn-sm admin-btn-filtrar">Buscar</button>
              </form>
              <a href="caja.php" class="btn btn-sm admin-btn-agregar"><i class="bi bi-plus-lg"></i> Nueva venta</a>
            </div>
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
              <tbody>
                <?php if (empty($facturas)): ?>
                  <tr>
                    <td colspan="7" class="text-center">No hay facturas registradas.</td>
                  </tr>
                  <?php else: foreach ($facturas as $f): ?>
                    <tr>
                      <td><?= str_pad((string)$f['IdFactura'], 4, '0', STR_PAD_LEFT) ?></td>
                      <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['Fecha']))) ?></td>
                      <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['FechaVencimiento']))) ?></td>
                      <td>
                        <?php if ($f['ClienteNombre']) { ?>
                          <?= htmlspecialchars($f['ClienteNombre'] . ' ' . $f['ClienteApellido']) ?><span class="admin-subtexto"> CI: <?= htmlspecialchars($f['ClienteCI']) ?></span>
                        <?php } else { ?>
                          <span class="admin-sin-dato">Sin cliente</span>
                        <?php } ?>
                      </td>
                      <td><?= htmlspecialchars($f['Rut']) ?></td>
                      <td class="text-end">$ <?= number_format((float)$f['Total'], 2, ',', '.') ?></td>
                      <td class="text-end">
                        <a class="btn btn-sm admin-btn-icono" href="factura.php?IdFactura=<?= $f['IdFactura'] ?>"><i class="bi bi-eye"></i> Ver</a>
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
