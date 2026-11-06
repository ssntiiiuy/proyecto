<?php
// abro la sesion para poder leer y guardar los datos del usuario
session_start();
require_once __DIR__ . '/includes/carrito.php';
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

// sin una cuenta iniciada no se puede ver ni cambiar la foto
if (empty($_SESSION['correo'])) {
  header('Location: ../LoginHTML.php');
  exit();
}

$conexion = (new Conexion())->establecerConexion();

// obtengo la cedula del usuario a partir del correo guardado en la sesion
$sqlUsuario = "SELECT u.CI, u.Nombre FROM USUARIO u INNER JOIN HACE h ON u.CI = h.CI WHERE h.Correo = :correo";
$stmt = $conexion->prepare($sqlUsuario);
$stmt->execute([':correo' => $_SESSION['correo']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  header('Location: ../../PHP/LoginHTML.php');
  exit();
}
$ci = (int) $usuario['CI'];

// El cliente puede ordenar por estos cuatro datos que ya existen en MASCOTA.
$opcionesOrden = ['Nombre' => ['Nombre', 'bi-sort-alpha-down'], 'Especie' => ['Tipo de animal', null], 'Raza' => ['Raza', 'bi-tags'], 'FechaNacimiento' => ['Fecha de nacimiento', 'bi-calendar3']];
$orden = $_GET['orden'] ?? 'Nombre';
// Si llega una columna o dirección desconocida, se usa el orden inicial.
$orden = isset($opcionesOrden[$orden]) ? $orden : 'Nombre';
$dir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

$mascotas = Mascota::listadoMascotasPorCliente($ci, $orden, $dir);

// La foto solo se guarda si la mascota aparece entre las del cliente.
$errorFoto = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardarFoto'])) {
  $idMascota = (int) ($_POST['IdMascota'] ?? 0);
  $mascotaPropia = null;
  foreach ($mascotas as $mascota) {
    if ((int) $mascota['IdMascota'] === $idMascota) {
      $mascotaPropia = $mascota;
      break;
    }
  }

  if ($mascotaPropia === null) {
    $errorFoto = 'No se encontró esa mascota en tu cuenta.';
  } else {
    $foto = Mascota::subirFoto($_FILES['Foto'] ?? []);
    if ($foto === null) {
      $errorFoto = 'Elegí una foto JPG, PNG o WebP de hasta 5 MB.';
    } elseif (Mascota::actualizarFoto($idMascota, $ci, $foto)) {
      // borro la foto anterior solo después de guardar la nueva
      if (!empty($mascotaPropia['Foto'])) {
        Mascota::borrarFoto($mascotaPropia['Foto']);
      }
      header('Location: mascotas.php?orden=' . $orden . '&dir=' . $dir . '&foto=ok');
      exit();
    } else {
      Mascota::borrarFoto($foto);
      $errorFoto = 'No se pudo guardar la foto. Intentá de nuevo.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis mascotas - Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../CSS/style.css?v=<?= filemtime(__DIR__ . '/../../CSS/style.css') ?>">
  <script src="../../JS/tema.js"></script>
</head>

<body>
  <?php
  $paginaActiva = 'mascotas';
  $mostrarCarrito = true;
  include __DIR__ . '/includes/header.php';
  ?>

  <main class="mascotas">
    <div class="container-xl px-3 px-lg-4 py-5">
      <div class="mascotas-encabezado text-center mb-4">
        <h1><span aria-hidden="true">🐾</span> Mis mascotas</h1>
        <p>Conocé a tus compañeros y consultá sus datos.</p>
      </div>

      <?php if ($errorFoto !== null) { ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorFoto) ?></div><?php } ?>
      <?php if (($_GET['foto'] ?? '') === 'ok') { ?><div class="alert alert-success" role="status">La foto se guardó.</div><?php } ?>

      <!-- El campo y la dirección se eligen por separado. -->
      <div class="mascotas-orden mb-4" aria-label="Ordenar mascotas">
        <strong>Ordenar por:</strong>
        <div class="mascotas-orden-grupo">
          <?php foreach ($opcionesOrden as $campo => [$etiqueta, $icono]) { ?>
            <a class="mascotas-orden-opcion <?= $orden === $campo ? 'activa' : '' ?>" href="?orden=<?= $campo ?>&dir=<?= $dir ?>" <?= $orden === $campo ? 'aria-current="true"' : '' ?>>
              <?php if ($icono === null) { ?>
                <span class="mascotas-orden-animales" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 7 4 5 2.5 8v4c0 1.5 1 2.5 2.5 2.5H6M18 7l2-2 1.5 3v4c0 1.5-1 2.5-2.5 2.5H18" />
                    <path d="M6 5.5C7.5 4.5 9.5 4 12 4s4.5.5 6 1.5V14c0 4-2.7 6.5-6 6.5S6 18 6 14V5.5Z" />
                    <path d="M9 11h.1M15 11h.1M10 15c1 1 3 1 4 0M11 13h2l-1 1.5-1-1.5Z" />
                  </svg>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 10 5 3l5 3h4l5-3 1 7v4c0 4-3.5 7-8 7s-8-3-8-7v-4Z" />
                    <path d="M9 12h.1M15 12h.1M11 15h2l-1 1-1-1ZM12 16v1M9 17c1 1 2 1 3 0 1 1 2 1 3 0M7 15l-4-1M7 17l-4 1M17 15l4-1M17 17l4 1" />
                  </svg>
                </span>
              <?php } else { ?><i class="bi <?= $icono ?>" aria-hidden="true"></i><?php } ?> <?= $etiqueta ?>
            </a>
          <?php } ?>
        </div>
        <div class="mascotas-orden-grupo mascotas-orden-direccion">
          <a class="mascotas-orden-opcion <?= $dir === 'asc' ? 'activa' : '' ?>" href="?orden=<?= $orden ?>&dir=asc" <?= $dir === 'asc' ? 'aria-current="true"' : '' ?>><i class="bi bi-arrow-up" aria-hidden="true"></i> Ascendente</a>
          <a class="mascotas-orden-opcion <?= $dir === 'desc' ? 'activa' : '' ?>" href="?orden=<?= $orden ?>&dir=desc" <?= $dir === 'desc' ? 'aria-current="true"' : '' ?>><i class="bi bi-arrow-down" aria-hidden="true"></i> Descendente</a>
        </div>
      </div>

      <!-- Cada mascota aparece en una tarjeta con su foto o una huella. -->
      <?php if (empty($mascotas)) { ?>
        <div class="mascotas-sin-datos text-center">No tenés mascotas registradas.</div>
      <?php } else { ?>
        <div class="row g-4">
          <?php foreach ($mascotas as $posicion => $m) { ?>
            <div class="col-lg-6">
              <article class="mascota-tarjeta h-100">
                <span class="mascota-numero">#<?= $posicion + 1 ?></span>
                <span class="mascota-marca" aria-hidden="true">🐾</span>
                <div class="mascota-contenido">
                  <!-- El círculo abre el selector; al elegir una foto aparece Guardar. -->
                  <form method="post" action="?orden=<?= $orden ?>&dir=<?= $dir ?>" enctype="multipart/form-data" class="mascota-foto-form">
                    <input type="hidden" name="IdMascota" value="<?= (int) $m['IdMascota'] ?>">
                    <input class="visually-hidden mascota-foto-input" type="file" id="foto-<?= (int) $m['IdMascota'] ?>" name="Foto" accept="image/jpeg,image/png,image/webp" required>
                    <label class="mascota-foto" for="foto-<?= (int) $m['IdMascota'] ?>">
                      <?php if (!empty($m['Foto'])) { ?>
                        <img src="../../img/mascotas/<?= htmlspecialchars(basename($m['Foto'])) ?>" alt="Foto de <?= htmlspecialchars($m['Nombre']) ?>">
                      <?php } else { ?>
                        <span aria-hidden="true">🐾</span>
                      <?php } ?>
                      <span class="mascota-foto-accion"><?= empty($m['Foto']) ? 'Añadir foto' : 'Cambiar foto' ?></span>
                    </label>
                    <button class="mascota-foto-guardar" type="submit" name="guardarFoto" value="1">Guardar foto</button>
                  </form>
                  <div class="mascota-informacion">
                    <h2><?= htmlspecialchars($m['Nombre']) ?></h2>
                    <dl>
                      <div><dt><span aria-hidden="true">🐾</span> Tipo de animal</dt><dd><?= htmlspecialchars($m['Especie']) ?></dd></div>
                      <div><dt><i class="bi bi-tags" aria-hidden="true"></i> Raza</dt><dd><?= htmlspecialchars($m['Raza']) ?></dd></div>
                      <div><dt><i class="bi bi-calendar3" aria-hidden="true"></i> Fecha de nacimiento</dt><dd><?= $m['FechaNacimiento'] ? date('d/m/Y', strtotime($m['FechaNacimiento'])) : '—' ?></dd></div>
                    </dl>
                  </div>
                </div>
              </article>
            </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
