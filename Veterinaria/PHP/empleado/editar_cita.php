<?php
$rolesPermitidos = ['Veterinario', 'Cajero', 'Peluquero', 'Cirujano'];
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/Cita.php';

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
$tipos = $tiposPorRol[$rol] ?? [];
$id = (int) ($_GET['id'] ?? 0);
$citasDelRol = Cita::listadoCitaPorTipo($tipos)['citas'];
$cita = null;
foreach ($citasDelRol as $citaDelRol) {
  if ((int) $citaDelRol['IdCita'] === $id) {
    $cita = $citaDelRol;
    break;
  }
}
if (!$cita) {
  header('Location: citas.php?msg=noexiste');
  exit();
}

$_SESSION['tokenCitas'] = $_SESSION['tokenCitas'] ?? bin2hex(random_bytes(32));
$error = null;
$estadoSeleccionado = $cita['Estado'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardarEstado'])) {
  $estadoSeleccionado = trim($_POST['Estado'] ?? '');
  $tokenValido = hash_equals($_SESSION['tokenCitas'], (string) ($_POST['tokenCitas'] ?? ''));
  $estadoValido = in_array($estadoSeleccionado, ['Pendiente', 'Confirmada', 'Cancelada'], true);

  if (!$tokenValido) {
    $error = 'La sesión del formulario venció. Volvé a abrir la cita.';
  } elseif (!$estadoValido) {
    $error = 'Elegí un estado válido.';
  } elseif (!(new Cita())->actualizarEstado($id, $estadoSeleccionado)) {
    $error = 'No se pudo actualizar el estado de la cita.';
  } else {
    header('Location: citas.php?msg=estado');
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar estado de cita - Panel Sienra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../../CSS/style.css">
  <link rel="stylesheet" href="../../CSS/admin.css">
  <script src="../../JS/tema.js"></script>
</head>
<body>
  <div class="admin-wrapper">
    <?php $paginaActiva = 'citas'; include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="admin-contenido">
      <?php $tituloPagina = 'Citas'; include __DIR__ . '/includes/topbar.php'; ?>
      <main class="container-fluid px-3 px-lg-4 py-4">
        <?php if ($error) { ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php } ?>
        <section class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Cambiar estado de cita</h2>
            <a class="admin-card-link" href="citas.php"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>
          <div class="p-3 p-lg-4">
            <dl class="row">
              <dt class="col-sm-3">Cliente</dt><dd class="col-sm-9"><?= htmlspecialchars($cita['ClienteNombre'] . ' ' . $cita['ClienteApellido']) ?></dd>
              <dt class="col-sm-3">Mascota</dt><dd class="col-sm-9"><?= htmlspecialchars($cita['MascotaNombre']) ?></dd>
              <dt class="col-sm-3">Servicio</dt><dd class="col-sm-9"><?= htmlspecialchars($cita['ServicioNombre']) ?></dd>
              <dt class="col-sm-3">Fecha y hora</dt><dd class="col-sm-9"><?= htmlspecialchars(date('d/m/Y', strtotime($cita['Fecha'])) . ' · ' . substr($cita['Hora'], 0, 5)) ?></dd>
            </dl>
            <form action="editar_cita.php?id=<?= $id ?>" method="post" class="mt-4">
              <input type="hidden" name="tokenCitas" value="<?= htmlspecialchars($_SESSION['tokenCitas']) ?>">
              <div class="mb-3">
                <label class="form-label" for="Estado">Estado</label>
                <select class="form-select" id="Estado" name="Estado" required>
                  <?php foreach (['Pendiente', 'Confirmada', 'Cancelada'] as $opcion) { ?>
                    <option value="<?= $opcion ?>" <?= $estadoSeleccionado === $opcion ? 'selected' : '' ?>><?= $opcion ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <a class="btn admin-btn-cancelar" href="citas.php">Volver</a>
                <button type="submit" class="btn admin-btn-agregar" name="guardarEstado">Guardar estado</button>
              </div>
            </form>
          </div>
        </section>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
