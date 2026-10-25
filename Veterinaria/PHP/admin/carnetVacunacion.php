<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/CarnetVacunacion.php';

$busqueda = trim($_GET['busqueda'] ?? '');
$carnets = CarnetVacunacion::listadoPorMascota($busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carnet de vacunación - Panel Sienra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>
<body>
  <div class="admin-wrapper">
    <?php $paginaActiva = 'carnet_vacunacion'; include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-contenido">
      <?php $tituloPagina = 'Carnet de vacunación'; include __DIR__ . '/includes/topbar.php'; ?>
      <main class="container-fluid px-3 px-lg-4 py-4">
        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Carnet de vacunación</h2>
          </div>

          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="carnet_vacunacion.php" method="get">
            <input class="form-control" type="text" name="busqueda" placeholder="Buscar por nombre de mascota..."
                   value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn admin-btn-agregar" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($busqueda !== ''): ?>
              <a class="btn admin-btn-cancelar" href="carnet_vacunacion.php">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Mascota</th>
                  <th scope="col">Tipo vacuna</th>
                  <th scope="col">Última vacunación</th>
                  <th scope="col">Próxima vacunación</th>
                  <th scope="col">Desparasitación</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($carnets)): ?>
                  <tr><td colspan="5" class="text-center">No hay registros.</td></tr>
                <?php else: foreach ($carnets as $c): ?>
                  <tr>
                    <td><?= htmlspecialchars($c['NombreMascota']) ?></td>
                    <td><?= htmlspecialchars($c['TipoVacunacion'] ?? '—') ?></td>
                    <td><?= $c['FechaUltimaVacunacion'] ? date('d/m/Y', strtotime($c['FechaUltimaVacunacion'])) : '—' ?></td>
                    <td><?= $c['FechaProximaVacunacion'] ? date('d/m/Y', strtotime($c['FechaProximaVacunacion'])) : '—' ?></td>
                    <td><?= htmlspecialchars($c['TipoDesparasitacion'] ?? '—') ?></td>
                  </tr>
                <?php endforeach; endif; ?>
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