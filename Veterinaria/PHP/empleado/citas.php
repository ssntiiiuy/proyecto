<?php
$rolesPermitidos = ['Veterinario', 'Cajero', 'Peluquero', 'Cirujano'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

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
  'Cajero'      => [],
];

if (!isset($tiposPorRol[$rol])) {
  header('Location: ../LoginHTML.php');
  exit();
}

$tipos = $tiposPorRol[$rol];

$estado = $_GET['estado'] ?? '';
$filtro = $_GET['filtro'] ?? null;

$datosCitas = Cita::listadoCitaPorTipo($tipos, $estado !== '' ? $estado : null, $filtro);
$citas = $datosCitas['citas'];

$mesSolicitado = $_GET['mes'] ?? date('Y-m');
$fechaMes = DateTime::createFromFormat('!Y-m', $mesSolicitado);
if (!$fechaMes || $fechaMes->format('Y-m') !== $mesSolicitado) {
  $fechaMes = new DateTime('first day of this month');
}
$diasEnMes = (int) $fechaMes->format('t');
$espaciosIniciales = (int) $fechaMes->format('N') - 1;
$mesAnterior = (clone $fechaMes)->modify('-1 month')->format('Y-m');
$mesSiguiente = (clone $fechaMes)->modify('+1 month')->format('Y-m');
$nombresMeses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
$citasPorDia = [];
$totalCitasMes = 0;
foreach ($citas as $cita) {
  if (substr($cita['Fecha'], 0, 7) === $fechaMes->format('Y-m')) {
    $citasPorDia[(int) date('j', strtotime($cita['Fecha']))][] = $cita;
    $totalCitasMes++;
  }
}

$parametrosFiltro = array_filter([
  'estado' => $estado
], function ($valor) {
  return $valor !== null && $valor !== '';
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda de citas - Panel Sienra</title>

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
      $tituloPagina = 'Agenda de citas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <section class="admin-card admin-calendario">
          <div class="admin-card-header admin-calendario-encabezado">
            <div>
              <h2>Agenda de citas</h2>
              <p><?= $totalCitasMes ?> <?= $totalCitasMes === 1 ? 'cita' : 'citas' ?> este mes</p>
            </div>
            <form class="d-flex align-items-center gap-2" action="citas.php" method="get">
              <input type="hidden" name="mes" value="<?= $fechaMes->format('Y-m') ?>">
              <?php if ($filtro === 'hoy') { ?>
                <input type="hidden" name="filtro" value="hoy">
              <?php } ?>
              <label class="visually-hidden" for="filtroEstado">Filtrar por estado</label>
              <select class="form-select form-select-sm admin-filtro" id="filtroEstado" name="estado">
                <option value="" <?= $estado === '' ? 'selected' : '' ?>>Todos los estados</option>
                <option value="Pendiente" <?= $estado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="Confirmada" <?= $estado === 'Confirmada' ? 'selected' : '' ?>>Confirmada</option>
                <option value="Cancelada" <?= $estado === 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
              </select>
              <button type="submit" class="btn btn-sm admin-btn-filtrar">Filtrar</button>
            </form>
          </div>

          <div class="admin-calendario-controles">
            <a class="btn admin-calendario-flecha" href="?<?= htmlspecialchars(http_build_query(['mes' => $mesAnterior] + $parametrosFiltro)) ?>" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></a>
            <h3><span><?= ucfirst($nombresMeses[(int) $fechaMes->format('n')]) ?></span> <?= $fechaMes->format('Y') ?></h3>
            <a class="btn admin-calendario-flecha" href="?<?= htmlspecialchars(http_build_query(['mes' => $mesSiguiente] + $parametrosFiltro)) ?>" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></a>
            <a class="admin-calendario-hoy" href="?<?= htmlspecialchars(http_build_query(['mes' => date('Y-m')] + $parametrosFiltro)) ?>">Este mes</a>
          </div>

          <div class="admin-calendario-scroll">
            <div class="admin-calendario-grid">
              <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana) { ?>
                <div class="admin-calendario-dia-semana"><?= $diaSemana ?></div>
              <?php } ?>
              <?php for ($espacio = 0; $espacio < $espaciosIniciales; $espacio++) { ?>
                <div class="admin-calendario-dia admin-calendario-vacio"></div>
              <?php } ?>
              <?php for ($dia = 1; $dia <= $diasEnMes; $dia++) { ?>
                <?php $fechaDia = $fechaMes->format('Y-m') . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT); ?>
                <div class="admin-calendario-dia <?= $fechaDia === date('Y-m-d') ? 'admin-calendario-dia-hoy' : '' ?>">
                  <span class="admin-calendario-numero"><?= $fechaDia === date('Y-m-d') ? '<span class="admin-calendario-etiqueta-hoy">Hoy</span>' : '' ?><span><?= $dia ?></span></span>
                  <?php if (!empty($citasPorDia[$dia])) { ?>
                    <?php foreach ($citasPorDia[$dia] as $cita) { ?>
                      <?php $estadoCita = match ($cita['Estado']) { 'Confirmada' => 'confirmada', 'Pendiente' => 'pendiente', 'Cancelada' => 'cancelada', default => 'otra' }; ?>
                      <div class="admin-calendario-cita estado-<?= $estadoCita ?>" title="<?= htmlspecialchars($cita['ClienteNombre'] . ' ' . $cita['ClienteApellido'] . ' · ' . $cita['MascotaNombre'] . ' · ' . $cita['ServicioNombre']) ?>">
                        <span class="admin-calendario-hora"><?= htmlspecialchars(substr($cita['Hora'], 0, 5)) ?></span>
                        <span><?= htmlspecialchars($cita['MascotaNombre']) ?></span>
                      </div>
                    <?php } ?>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </div>
        </section>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
