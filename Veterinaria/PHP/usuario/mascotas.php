<?php
// abro la sesion para poder leer y guardar los datos del usuario
session_start();
require_once __DIR__ . '/includes/carrito.php';
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

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
$opcionesOrden = ['Nombre' => ['Nombre', 'bi-sort-alpha-down'], 'Especie' => ['Tipo de animal', 'bi-heart-pulse'], 'Raza' => ['Raza', 'bi-tags'], 'FechaNacimiento' => ['Fecha de nacimiento', 'bi-calendar3']];
$orden = $_GET['orden'] ?? 'Nombre';
// Si llega una columna o dirección desconocida, se usa el orden inicial.
$orden = isset($opcionesOrden[$orden]) ? $orden : 'Nombre';
$dir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

$mascotas = Mascota::listadoMascotasPorCliente($ci, $orden, $dir);
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
        <h1><i class="bi bi-heart-fill" aria-hidden="true"></i> Mis mascotas</h1>
        <p>Conocé a tus compañeros y consultá sus datos.</p>
      </div>

      <!-- El campo y la dirección se eligen por separado. -->
      <div class="mascotas-orden mb-4" aria-label="Ordenar mascotas">
        <strong>Ordenar por:</strong>
        <div class="mascotas-orden-grupo">
          <?php foreach ($opcionesOrden as $campo => [$etiqueta, $icono]) { ?>
            <a class="mascotas-orden-opcion <?= $orden === $campo ? 'activa' : '' ?>" href="?orden=<?= $campo ?>&dir=<?= $dir ?>" <?= $orden === $campo ? 'aria-current="true"' : '' ?>>
              <i class="bi <?= $icono ?>" aria-hidden="true"></i> <?= $etiqueta ?>
            </a>
          <?php } ?>
        </div>
        <div class="mascotas-orden-grupo mascotas-orden-direccion">
          <a class="mascotas-orden-opcion <?= $dir === 'asc' ? 'activa' : '' ?>" href="?orden=<?= $orden ?>&dir=asc" <?= $dir === 'asc' ? 'aria-current="true"' : '' ?>><i class="bi bi-arrow-up" aria-hidden="true"></i> Ascendente</a>
          <a class="mascotas-orden-opcion <?= $dir === 'desc' ? 'activa' : '' ?>" href="?orden=<?= $orden ?>&dir=desc" <?= $dir === 'desc' ? 'aria-current="true"' : '' ?>><i class="bi bi-arrow-down" aria-hidden="true"></i> Descendente</a>
        </div>
      </div>

      <!-- Cada mascota aparece en una tarjeta; el círculo espera la futura foto. -->
      <?php if (empty($mascotas)) { ?>
        <div class="mascotas-sin-datos text-center">No tenés mascotas registradas.</div>
      <?php } else { ?>
        <div class="row g-4">
          <?php foreach ($mascotas as $posicion => $m) { ?>
            <div class="col-lg-6">
              <article class="mascota-tarjeta h-100">
                <span class="mascota-numero">#<?= $posicion + 1 ?></span>
                <span class="mascota-marca" aria-hidden="true"><i class="bi bi-heart-fill"></i></span>
                <div class="mascota-contenido">
                  <div class="mascota-foto" aria-label="Foto pendiente de <?= htmlspecialchars($m['Nombre']) ?>">
                    <i class="bi bi-heart-pulse" aria-hidden="true"></i>
                  </div>
                  <div class="mascota-informacion">
                    <h2><?= htmlspecialchars($m['Nombre']) ?></h2>
                    <dl>
                      <div><dt><i class="bi bi-heart-pulse" aria-hidden="true"></i> Tipo de animal</dt><dd><?= htmlspecialchars($m['Especie']) ?></dd></div>
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
