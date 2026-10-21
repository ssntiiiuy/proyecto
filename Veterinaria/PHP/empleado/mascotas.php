<?php
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

/* funcion para guardar los datos */
$datosMascotas = Mascota::listadoMascotas();
$mascotas = $datosMascotas['mascotas'];

$conexion = (new Conexion())->establecerConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
  $datosForm = $_POST;

  $nombre = trim($_POST['Nombre']);
  $especie = trim($_POST['Especie']);
  $raza = trim($_POST['Raza']);
  $fechaNacimiento = trim($_POST['FechaNacimiento']);
  $ciDueño = trim($_POST['CI']);

  if (!empty($fechaNacimiento) && $fechaNacimiento > date('Y-m-d')) {
    $error = "La fecha de nacimiento no puede ser posterior a hoy.";
  } else {
    $mascota = new Mascota();
    $resultado = $mascota->crearMascota($nombre, $especie, $raza, $fechaNacimiento, $ciDueño);

    if ($resultado) {
      header("Location: mascotas.php");
      exit();
    } else {
      $error = "No se pudo completar el registro.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mascotas - Panel Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css">
</head>

<body>

  <div class="admin-wrapper">
    <?php
    $paginaActiva = 'mascotas';
    include __DIR__ . '/includes/sidebar.php';
    ?>

    <div class="admin-contenido">
      <?php
      $tituloPagina = 'Mascotas';
      include __DIR__ . '/includes/topbar.php';
      ?>

      <main class="container-fluid px-3 px-lg-4 py-4">

        <div class="admin-card">
          <div class="admin-card-header flex-wrap">
            <h2>Listado de mascotas</h2>
            <button class="btn btn-sm admin-btn-agregar" type="button" data-bs-toggle="modal" data-bs-target="#modalMascota">
              <i class="bi bi-plus-lg"></i> Agregar mascota
            </button>
          </div>

          <div class="table-responsive">
            <table class="table admin-tabla mb-0">
              <thead>
                <tr>
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Especie</th>
                  <th scope="col">Raza</th>
                  <th scope="col">Fecha de nacimiento</th>
                  <th scope="col">Dueño</th>
                </tr>
              </thead>
              <tbody>

                <?php foreach ($mascotas as $masc): ?>

                  <tr>
                    <td><?= htmlspecialchars($masc['IdMascota']) ?></td>
                    <td><?= htmlspecialchars($masc['Nombre']) ?></td>
                    <td><?= htmlspecialchars($masc['Especie']) ?></td>
                    <td><?= htmlspecialchars($masc['Raza']) ?></td>
                    <td><?= date('d/m/Y', strtotime($masc['FechaNacimiento'])) ?></td>
                    <td><?= htmlspecialchars($masc['NombreDueño'] . ' ' . $masc['ApellidoDueño']) ?><br><span class="admin-subtexto">CI: <?= htmlspecialchars($masc['CI']) ?></span></td>
                    </td>
                  </tr>
                <?php endforeach; ?>

              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- agregar mascota -->
  <div class="modal fade admin-modal" id="modalMascota" tabindex="-1" aria-labelledby="modalMascotaTitulo" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" action="#" method="post">
        <div class="modal-header">
          <h2 class="modal-title" id="modalMascotaTitulo">Agregar mascota</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-sm-12">
              <label class="form-label" for="Nombre">Nombre</label>
              <input class="form-control" type="text" id="Nombre" name="Nombre" placeholder="Rex" value="<?= htmlspecialchars($datosForm['Nombre'] ?? '') ?>" required>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Especie">Especie</label>
              <select class="form-select" id="Especie" name="Especie" required>
                <option value="" <?= empty($datosForm['Especie']) ? 'selected' : '' ?> disabled>Elegir especie</option>
                <option value="Perro" <?= (($datosForm['Especie'] ?? '') === 'Perro') ? 'selected' : '' ?>>Perro</option>
                <option value="Gato" <?= (($datosForm['Especie'] ?? '') === 'Gato') ? 'selected' : '' ?>>Gato</option>
                <option value="Otro" <?= (($datosForm['Especie'] ?? '') === 'Otro') ? 'selected' : '' ?>>Otro</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="Raza">Raza</label>
              <input class="form-control" type="text" id="Raza" name="Raza" placeholder="Labrador" value="<?= htmlspecialchars($datosForm['Raza'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="FechaNacimiento">Fecha de nacimiento</label>
              <input class="form-control" type="date" id="FechaNacimiento" name="FechaNacimiento"
                max="<?= date('Y-m-d') ?>"
                value="<?= htmlspecialchars($datosForm['FechaNacimiento'] ?? '') ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="CI">Dueño</label>
              <select class="form-select" id="CI" name="CI" required>
                <option value="" <?= empty($datosForm['CI']) ? 'selected' : '' ?> disabled>Elegir cliente</option>
                <?php
                $stmtCli = $conexion->query("SELECT u.CI, u.Nombre, u.Apellido FROM CLIENTE c INNER JOIN USUARIO u ON c.CI = u.CI");
                while ($cli = $stmtCli->fetch(PDO::FETCH_ASSOC)):
                ?>
                  <option value="<?= $cli['CI'] ?>" <?= (($datosForm['CI'] ?? '') == $cli['CI']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cli['Nombre'] . ' ' . $cli['Apellido']) ?> (CI: <?= $cli['CI'] ?>)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn admin-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn admin-btn-agregar" name="guardar">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <?php if ($error): ?>
    <script>
      new bootstrap.Modal(document.getElementById('modalMascota')).show();
    </script>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
</body>

</html>