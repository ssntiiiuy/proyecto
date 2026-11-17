<?php
$rolesPermitidos = ['Veterinario', 'Cajero', 'Peluquero', 'Cirujano'];
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/Cita.php';
require_once __DIR__ . '/../includes/horarios_citas.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$rol = $_SESSION['rol'] ?? '';
$tiposPorRol = [
  'Veterinario' => ['Consulta', 'Vacunacion'],
  'Cirujano' => ['Cirugia'],
  'Peluquero' => ['Peluqueria'],
  'Cajero' => [],
];
if (!array_key_exists($rol, $tiposPorRol)) {
  header('Location: ../LoginHTML.php');
  exit();
}
$tipos = $tiposPorRol[$rol];
$hoyAgenda = fechaActualMontevideo();
$_SESSION['tokenCitas'] = $_SESSION['tokenCitas'] ?? bin2hex(random_bytes(32));
$tokenCitas = $_SESSION['tokenCitas'];
$mesSolicitado = $_GET['mes'] ?? substr($hoyAgenda, 0, 7);
$fechaMes = DateTime::createFromFormat('!Y-m', $mesSolicitado);
if (!$fechaMes || $fechaMes->format('Y-m') !== $mesSolicitado) {
  $fechaMes = new DateTime(substr($hoyAgenda, 0, 7) . '-01');
}
$estado = trim($_GET['estado'] ?? '');
if (!in_array($estado, ['', 'Pendiente', 'Confirmada', 'Cancelada'], true)) {
  $estado = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionEstado'])) {
  $idCita = (int) ($_POST['IdCita'] ?? 0);
  $accion = $_POST['accionEstado'];
  $nuevoEstado = ['confirmar' => 'Confirmada', 'cancelar' => 'Cancelada'][$accion] ?? null;
  $resultado = false;
  if ($nuevoEstado && hash_equals($tokenCitas, (string) ($_POST['tokenCitas'] ?? ''))) {
    $citasRol = Cita::listadoCitaPorTipo($tipos)['citas'];
    foreach ($citasRol as $cita) {
      if ((int) $cita['IdCita'] === $idCita && $cita['Estado'] === 'Pendiente'
        && ($accion !== 'confirmar' || $cita['Fecha'] === $hoyAgenda)) {
        $resultado = (new Cita())->cambiarEstado($idCita, $nuevoEstado);
        break;
      }
    }
  }
  $params = ['mes' => $_GET['mes'] ?? $fechaMes->format('Y-m')];
  if ($estado !== '') $params['estado'] = $estado;
  $params['msg'] = $resultado ? 'estado' : 'estado_error';
  header('Location: citas.php?' . http_build_query($params));
  exit();
}

$mensajes = [
  'estado' => ['success', 'El estado de la cita se actualizó.'],
  'estado_error' => ['danger', 'No se pudo cambiar el estado. Verificá que siga pendiente y que corresponda a tu agenda.'],
];
$mensaje = $mensajes[$_GET['msg'] ?? ''] ?? null;
$datosCitas = Cita::listadoCitaPorTipo($tipos, $estado !== '' ? $estado : null);
$citas = $datosCitas['citas'];
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
$parametrosFiltro = $estado !== '' ? ['estado' => $estado] : [];
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
    <?php $paginaActiva = 'agenda'; include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-contenido">
      <?php $tituloPagina = 'Mi agenda'; include __DIR__ . '/includes/topbar.php'; ?>
      <main class="container-fluid px-3 px-lg-4 py-4">
        <?php if ($mensaje) { ?><div class="alert alert-<?= htmlspecialchars($mensaje[0]) ?>"><?= htmlspecialchars($mensaje[1]) ?></div><?php } ?>
        <section class="admin-card admin-calendario">
          <div class="admin-card-header admin-calendario-encabezado">
            <div>
              <h2>Mi agenda</h2>
              <p><?= $totalCitasMes ?> <?= $totalCitasMes === 1 ? 'cita' : 'citas' ?> este mes</p>
              <p class="mb-0 small text-muted">Horario: <?= htmlspecialchars(resumenHorarioClinica()) ?></p>
            </div>
            <form class="d-flex align-items-center gap-2" action="citas.php" method="get">
              <input type="hidden" name="mes" value="<?= $fechaMes->format('Y-m') ?>">
              <label class="visually-hidden" for="filtroEstado">Filtrar por estado</label>
              <select class="form-select form-select-sm admin-filtro" id="filtroEstado" name="estado">
                <option value="">Todos los estados</option>
                <?php foreach (['Pendiente', 'Confirmada', 'Cancelada'] as $opcion) { ?><option value="<?= $opcion ?>" <?= $estado === $opcion ? 'selected' : '' ?>><?= $opcion ?></option><?php } ?>
              </select>
              <button type="submit" class="btn btn-sm admin-btn-filtrar">Filtrar</button>
            </form>
          </div>
          <div class="admin-calendario-controles">
            <a class="btn admin-calendario-flecha" href="?<?= htmlspecialchars(http_build_query(['mes' => $mesAnterior] + $parametrosFiltro)) ?>" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></a>
            <h3><span><?= ucfirst($nombresMeses[(int) $fechaMes->format('n')]) ?></span> <?= $fechaMes->format('Y') ?></h3>
            <a class="btn admin-calendario-flecha" href="?<?= htmlspecialchars(http_build_query(['mes' => $mesSiguiente] + $parametrosFiltro)) ?>" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></a>
            <a class="admin-calendario-hoy" href="?<?= htmlspecialchars(http_build_query(['mes' => substr($hoyAgenda, 0, 7)] + $parametrosFiltro)) ?>">Este mes</a>
          </div>
          <div class="admin-calendario-scroll">
            <div class="admin-calendario-grid">
              <?php foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaSemana) { ?><div class="admin-calendario-dia-semana"><?= $diaSemana ?></div><?php } ?>
              <?php for ($espacio = 0; $espacio < $espaciosIniciales; $espacio++) { ?><div class="admin-calendario-dia admin-calendario-vacio"></div><?php } ?>
              <?php for ($dia = 1; $dia <= $diasEnMes; $dia++) { ?>
                <?php $fechaDia = $fechaMes->format('Y-m') . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT); ?>
                <div class="admin-calendario-dia <?= $fechaDia === $hoyAgenda ? 'admin-calendario-dia-hoy' : '' ?>">
                  <span class="admin-calendario-numero"><?= $fechaDia === $hoyAgenda ? '<span class="admin-calendario-etiqueta-hoy">Hoy</span>' : '' ?><span><?= $dia ?></span></span>
                  <?php foreach ($citasPorDia[$dia] ?? [] as $cita) { ?>
                    <?php $estadoCss = ['Confirmada' => 'confirmada', 'Pendiente' => 'pendiente', 'Cancelada' => 'cancelada'][$cita['Estado']] ?? 'otra'; ?>
                    <a class="admin-calendario-cita estado-<?= $estadoCss ?> text-decoration-none" href="citas.php?<?= htmlspecialchars(http_build_query(['fecha' => $fechaDia, 'cita' => (int) $cita['IdCita']])) ?>" title="Abrir esta cita en el listado">
                      <span class="admin-calendario-hora"><?= htmlspecialchars(substr($cita['Hora'], 0, 5)) ?></span>
                      <span><?= htmlspecialchars($cita['MascotaNombre']) ?></span>
                    </a>
                    <?php if ($cita['Estado'] === 'Pendiente') { ?>
                      <form method="post" class="d-flex flex-wrap gap-1 mb-2">
                        <input type="hidden" name="IdCita" value="<?= (int) $cita['IdCita'] ?>">
                        <input type="hidden" name="tokenCitas" value="<?= htmlspecialchars($tokenCitas) ?>">
                        <input type="hidden" name="mes" value="<?= htmlspecialchars($fechaMes->format('Y-m')) ?>">
                        <input type="hidden" name="estado" value="<?= htmlspecialchars($estado) ?>">
                        <?php if ($cita['Fecha'] === $hoyAgenda) { ?><button class="btn btn-sm btn-outline-success" name="accionEstado" value="confirmar">Confirmar llegada</button><?php } ?>
                        <button class="btn btn-sm btn-outline-danger" name="accionEstado" value="cancelar">Cancelar</button>
                      </form>
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
