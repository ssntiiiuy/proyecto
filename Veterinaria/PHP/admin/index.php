<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Cita.php';
require_once __DIR__ . '/../clases/Producto.php';

$datosCitas = Cita::listadoCita();
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

$citasHoy = Cita::contarCitasHoy();
$stockBajo = Producto::contarStockBajo();
$porVencer = Producto::contarPorVencer();

$conexion = (new Conexion())->establecerConexion();
$clientesRegistrados = (int)$conexion->query("SELECT COUNT(*) FROM CLIENTE")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Panel Sienra</title>

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
    $paginaActiva = 'dashboard';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Dashboard';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <!-- lleva a citas filtrado por "hoy" -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <a href="citas.php?filtro=hoy" class="admin-stat admin-stat-info text-decoration-none">
              <div>
                <p class="admin-stat-label">Citas de hoy</p>
                <p class="admin-stat-valor"><?= $citasHoy ?></p>
              </div>
              <i class="bi bi-calendar-check"></i>
            </a>
          </div>

          <!-- modulo con href para stock bajo y productos por vencer -->
          <div class="col-sm-6 col-xl-3">
            <a href="productos.php?filtro=stock_bajo" class="admin-stat admin-stat-danger text-decoration-none">
              <div>
                <p class="admin-stat-label">PRODUCTOS CON STOCK BAJO</p>
                <p class="admin-stat-valor"><?= Producto::contarStockBajo() ?></p>
              </div>
              <i class="bi bi-box-seam"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="productos.php?filtro=por_vencer" class="admin-stat admin-stat-warning text-decoration-none">
              <div>
                <p class="admin-stat-label">PRODUCTOS POR VENCER</p>
                <p class="admin-stat-valor"><?= Producto::contarPorVencer() ?></p>
              </div>
              <i class="bi bi-hourglass-split"></i>
            </a>
          </div>

          <div class="col-sm-6 col-xl-3">
            <a href="usuarios.php" class="admin-stat admin-stat-success text-decoration-none">
              <div>
                <p class="admin-stat-label">Clientes registrados</p>
                <p class="admin-stat-valor"><?= $clientesRegistrados ?></p>
              </div>
              <i class="bi bi-people"></i>
            </a>
          </div>
        </div>

        <section class="admin-card admin-calendario">
          <div class="admin-card-header admin-calendario-encabezado">
            <div>
              <h2>Agenda de citas</h2>
              <p><?= $totalCitasMes ?> <?= $totalCitasMes === 1 ? 'cita' : 'citas' ?> este mes</p>
            </div>
            <a class="btn admin-calendario-todas" href="citas.php">Ver todas <i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="admin-calendario-controles">
            <a class="btn admin-calendario-flecha" href="?mes=<?= $mesAnterior ?>" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></a>
            <h3><span><?= ucfirst($nombresMeses[(int) $fechaMes->format('n')]) ?></span> <?= $fechaMes->format('Y') ?></h3>
            <a class="btn admin-calendario-flecha" href="?mes=<?= $mesSiguiente ?>" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></a>
            <a class="admin-calendario-hoy" href="?mes=<?= date('Y-m') ?>">Hoy</a>
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
                    <a class="admin-calendario-cita estado-<?= $estadoCita ?>" href="editar_cita.php?id=<?= (int) $cita['IdCita'] ?>" title="<?= htmlspecialchars($cita['ClienteNombre'] . ' ' . $cita['ClienteApellido'] . ' · ' . $cita['MascotaNombre'] . ' · ' . $cita['ServicioNombre']) ?>">
                      <span class="admin-calendario-hora"><?= htmlspecialchars(substr($cita['Hora'], 0, 5)) ?></span>
                      <span><?= htmlspecialchars($cita['MascotaNombre']) ?></span>
                    </a>
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
