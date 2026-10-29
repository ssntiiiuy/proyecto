<?php
$rolesPermitidos = ['admin'];
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Servicio.php';

/* filtro del listado, para volver con el mismo al terminar */
$filtrosListado = [];
if (!empty($_GET['estado'])) {
  $filtrosListado['estado'] = $_GET['estado'];
}
$urlListado = 'servicios.php?' . http_build_query($filtrosListado);

/* se busca el servicio que llega en la URL: editar_servicio.php?id=5 */
$id = (int) ($_GET['id'] ?? 0);
$servicio = Servicio::buscarPorId($id);

if (!$servicio) {
  header("Location: servicios.php?" . http_build_query(['msg' => 'noexiste'] + $filtrosListado));
  exit();
}

/* el tipo sale de la base y no se puede cambiar */
$tipo = $servicio['Tipo'];
$nombresTipo = ['Consulta' => 'Consulta', 'Vacunacion' => 'Vacunación', 'Peluqueria' => 'Peluquería', 'Cirugia' => 'Cirugía'];
$clasesTipo = ['Consulta' => 'badge-tipo-consulta', 'Vacunacion' => 'badge-tipo-vacunacion', 'Peluqueria' => 'badge-tipo-peluqueria', 'Cirugia' => 'badge-tipo-cirugia'];

$error = null;
$datosForm = $servicio; /* el formulario arranca con los datos de la base */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre'] ?? '');
  $precio = trim($_POST['Precio'] ?? '');
  $descripcion = trim($_POST['Descripcion'] ?? '');
  $duracion = trim($_POST['Duracion'] ?? '');

  /* se valida antes de guardar */
  if ($nombre === '') {
    $error = "Falta el nombre del servicio.";
  } elseif ($duracion === '') {
    $error = "Falta la duración del servicio.";
  } elseif (!ctype_digit($duracion) || (int)$duracion < 1) {
    $error = "La duración debe ser un número entero mayor a 0.";
  } elseif ($precio === '') {
    $error = "Falta el precio del servicio.";
  } elseif (!is_numeric($precio) || $precio < 0) {
    $error = "El precio no puede ser negativo.";
  } else {
    try {
      $resultado = (new Servicio())->editarServicio($id, $tipo, $nombre, (float)$precio, $descripcion, (int)$duracion, $_POST);
    } catch (Throwable $e) {
      $resultado = false;
    }

    if ($resultado) {
      header("Location: servicios.php?" . http_build_query(['msg' => 'editado'] + $filtrosListado));
      exit();
    } else {
      $error = "No se pudieron guardar los cambios. Revisá los datos.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar servicio - Panel Sienra</title>

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
    $paginaActiva = 'servicios';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Servicios';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <?php if ($error) { ?>
          <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Editar servicio (Id <?= $id ?>)</h2>
            <a class="admin-card-link" href="<?= htmlspecialchars($urlListado) ?>"><i class="bi bi-arrow-left"></i> Volver al listado</a>
          </div>

          <form class="p-3 p-lg-4" action="editar_servicio.php?<?= htmlspecialchars(http_build_query(['id' => $id] + $filtrosListado)) ?>" method="post">
            <div class="mb-3">
              <span class="admin-subtexto">Tipo:</span>
              <span class="badge <?= $clasesTipo[$tipo] ?? 'bg-secondary' ?>"><?= htmlspecialchars($nombresTipo[$tipo] ?? $tipo) ?></span>
              <span class="admin-subtexto">(no se puede cambiar)</span>
            </div>

            <div class="row g-3">
              <div class="col-sm-12">
                <label class="form-label" for="Nombre">Nombre</label>
                <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Consulta general" maxlength="100" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="Descripcion">Descripción</label>
                <textarea class="form-control" id="Descripcion" name="Descripcion" rows="2" maxlength="255" placeholder="Revisión clínica completa..."><?= htmlspecialchars($datosForm['Descripcion'] ?? '') ?></textarea>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Duracion">Duración (minutos)</label>
                <input class="form-control" type="number" id="Duracion" name="Duracion" min="5" step="5" placeholder="30" value="<?= htmlspecialchars($datosForm['Duracion'] ?? '') ?>" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="Precio">Precio ($)</label>
                <input class="form-control" type="number" id="Precio" name="Precio" min="0" step="0.01" placeholder="900" value="<?= htmlspecialchars($datosForm['Precio'] ?? '') ?>" required>
              </div>

              <!-- campos propios del subtipo: se muestran solo los del tipo de este servicio -->
              <?php if ($tipo === 'Consulta') { ?>
                <div class="col-sm-12">
                  <label class="form-label" for="Motivo">Motivo</label>
                  <input class="form-control" type="text" id="Motivo" name="Motivo" maxlength="255" value="<?= htmlspecialchars($datosForm['Motivo'] ?? '') ?>">
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="EsEmergencia">¿Es emergencia?</label>
                  <select class="form-select" id="EsEmergencia" name="EsEmergencia">
                    <option value="0" <?= (($datosForm['EsEmergencia'] ?? 0) == 1) ? '' : 'selected' ?>>No</option>
                    <option value="1" <?= (($datosForm['EsEmergencia'] ?? 0) == 1) ? 'selected' : '' ?>>Sí</option>
                  </select>
                </div>
                <div class="col-sm-12">
                  <label class="form-label" for="IndicacionesRecetadas">Indicaciones</label>
                  <textarea class="form-control" id="IndicacionesRecetadas" name="IndicacionesRecetadas" maxlength="255"><?= htmlspecialchars($datosForm['IndicacionesRecetadas'] ?? '') ?></textarea>
                </div>
              <?php } elseif ($tipo === 'Vacunacion') { ?>
                <div class="col-sm-4">
                  <label class="form-label" for="LoteVacuna">Lote</label>
                  <input class="form-control" type="text" id="LoteVacuna" name="LoteVacuna" maxlength="150" value="<?= htmlspecialchars($datosForm['LoteVacuna'] ?? '') ?>">
                </div>
                <div class="col-sm-4">
                  <label class="form-label" for="TipoVacuna">Tipo de vacuna</label>
                  <input class="form-control" type="text" id="TipoVacuna" name="TipoVacuna" maxlength="100" value="<?= htmlspecialchars($datosForm['TipoVacuna'] ?? '') ?>">
                </div>
                <div class="col-sm-4">
                  <label class="form-label" for="LaboratorioFabricante">Laboratorio</label>
                  <input class="form-control" type="text" id="LaboratorioFabricante" name="LaboratorioFabricante" maxlength="100" value="<?= htmlspecialchars($datosForm['LaboratorioFabricante'] ?? '') ?>">
                </div>
              <?php } elseif ($tipo === 'Peluqueria') { ?>
                <div class="col-sm-6">
                  <label class="form-label" for="TipoCorte">Tipo de corte</label>
                  <input class="form-control" type="text" id="TipoCorte" name="TipoCorte" maxlength="100" value="<?= htmlspecialchars($datosForm['TipoCorte'] ?? '') ?>">
                </div>
                <div class="col-sm-6">
                  <label class="form-label" for="TipoShampoo">Tipo de shampoo</label>
                  <input class="form-control" type="text" id="TipoShampoo" name="TipoShampoo" maxlength="100" value="<?= htmlspecialchars($datosForm['TipoShampoo'] ?? '') ?>">
                </div>
              <?php } elseif ($tipo === 'Cirugia') { ?>
                <div class="col-sm-4">
                  <label class="form-label" for="TipoAnestesia">Anestesia</label>
                  <input class="form-control" type="text" id="TipoAnestesia" name="TipoAnestesia" maxlength="100" value="<?= htmlspecialchars($datosForm['TipoAnestesia'] ?? '') ?>">
                </div>
                <div class="col-sm-4">
                  <label class="form-label" for="RiesgoQuirurgico">Riesgo quirúrgico</label>
                  <input class="form-control" type="text" id="RiesgoQuirurgico" name="RiesgoQuirurgico" maxlength="100" value="<?= htmlspecialchars($datosForm['RiesgoQuirurgico'] ?? '') ?>">
                </div>
                <div class="col-sm-4">
                  <label class="form-label" for="AutorizacionFirmada">¿Autorización firmada?</label>
                  <select class="form-select" id="AutorizacionFirmada" name="AutorizacionFirmada">
                    <option value="0" <?= (($datosForm['AutorizacionFirmada'] ?? 0) == 1) ? '' : 'selected' ?>>No</option>
                    <option value="1" <?= (($datosForm['AutorizacionFirmada'] ?? 0) == 1) ? 'selected' : '' ?>>Sí</option>
                  </select>
                </div>
              <?php } ?>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a class="btn admin-btn-cancelar" href="<?= htmlspecialchars($urlListado) ?>">Cancelar</a>
              <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar cambios</button>
            </div>
          </form>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
