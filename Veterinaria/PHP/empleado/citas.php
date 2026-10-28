<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Cita.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$rol = $_SESSION['rol'] ?? null;

/* que tipo(s) de servicio le corresponden a cada rol clinico */
$tiposPorRol = [
  'Veterinario' => ['Consulta', 'Vacunacion'],
  'Cirujano'    => ['Cirugia'],
  'Peluquero'   => ['Peluqueria'],
];

if (!isset($tiposPorRol[$rol])) {
  header('Location: /PHP/LoginHTML.php');
  exit();
}

$tipos = $tiposPorRol[$rol];

$estado = $_GET['estado'] ?? '';
$filtro = $_GET['filtro'] ?? null;

$datosCitas = Cita::listadoCitaPorTipo($tipos, $estado !== '' ? $estado : null, $filtro);
$citas = $datosCitas['citas'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi agenda - Panel Sienra</title>

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
    $paginaActiva = 'agenda';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Mi agenda';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Mi agenda</h2>
            <form class="d-flex align-items-center gap-2" action="citas.php" method="get">
              <label class="visually-hidden" for="filtroEstado">Filtrar por estado</label>
              <select class="form-select form-select-sm admin-filtro" id="filtroEstado" name="estado">
                <option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos los estados</option>
                <option value="Pendiente" <?= $estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Confirmada" <?= $estado === 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                <option value="Cancelada" <?= $estado === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
              <button type="submit" class="btn btn-sm admin-btn-filtrar">Filtrar</button>
              <?php if ($filtro === 'hoy') { ?>
                <a class="btn btn-sm admin-btn-cancelar" href="citas.php?estado=<?= urlencode($estado) ?>">Ver todas</a>
              <?php } else { ?>
                <a class="btn btn-sm admin-btn-cancelar" href="citas.php?filtro=hoy">Solo hoy</a>
              <?php } ?>
            </form>
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
              <tbody>
                <?php if (empty($citas)): ?>
                  <tr>
                    <td colspan="6" class="text-center">No hay citas para mostrar.</td>
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
