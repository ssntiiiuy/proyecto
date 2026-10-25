<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/HistorialClinico.php';

$busqueda = trim($_GET['busqueda'] ?? '');
$historiales = HistorialClinico::listadoPorMascota($busqueda);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial clínico - Panel Sienra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>
<body>
  <div class="admin-wrapper">
    <?php $paginaActiva = 'historial_clinico'; include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-contenido">
      <?php $tituloPagina = 'Historial clínico'; include __DIR__ . '/includes/topbar.php'; ?>
      <main class="container-fluid px-3 px-lg-4 py-4">
        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Historial clínico</h2>
          </div>

          <form class="d-flex gap-2 mb-3 mt-3 mx-3 mx-lg-4" action="historial_clinico.php" method="get">
            <input class="form-control" type="text" name="busqueda" placeholder="Buscar por nombre de mascota..."
                   value="<?= htmlspecialchars($busqueda) ?>">
            <button class="btn admin-btn-agregar" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($busqueda !== ''): ?>
              <a class="btn admin-btn-cancelar" href="historial_clinico.php">Limpiar</a>
            <?php endif; ?>
          </form>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Mascota</th>
                  <th scope="col">Fecha</th>
                  <th scope="col">Diagnóstico</th>
                  <th scope="col">Tratamiento</th>
                  <th scope="col">Peso</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($historiales)): ?>
                  <tr><td colspan="5" class="text-center">No hay registros.</td></tr>
                <?php else: foreach ($historiales as $h): ?>
                  <tr>
                    <td><?= htmlspecialchars($h['NombreMascota']) ?></td>
                    <td><?= $h['Fecha'] ? date('d/m/Y', strtotime($h['Fecha'])) : '—' ?></td>
                    <td><?= htmlspecialchars($h['Diagnóstico'] ?? '') ?></td>
                    <td><?= htmlspecialchars($h['Tratamiento'] ?? '') ?></td>
                    <td><?= htmlspecialchars($h['Peso'] ?? '—') ?> kg</td>
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