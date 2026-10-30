<?php
session_start();
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../clases/Mascota.php';

$conexion = (new Conexion())->establecerConexion();

$sqlUsuario = "SELECT u.CI, u.Nombre FROM USUARIO u INNER JOIN HACE h ON u.CI = h.CI WHERE h.Correo = :correo";
$stmt = $conexion->prepare($sqlUsuario);
$stmt->execute([':correo' => $_SESSION['correo']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  header('Location: ../../PHP/LoginHTML.php');
  exit();
}
$ci = (int) $usuario['CI'];

// orden por columna
$orden = $_GET['orden'] ?? 'Nombre';
$dir = $_GET['dir'] ?? 'asc';
$dirSiguiente = ($dir === 'asc') ? 'desc' : 'asc'; // para que el link invierta al clickear de nuevo

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

  <link rel="stylesheet" href="../../CSS/style.css">
  <script src="../../JS/tema.js"></script>
</head>

<body>
  <?php
  $paginaActiva = 'mascotas';
  $mostrarCarrito = false;
  include __DIR__ . '/includes/header.php';
  ?>

  <main class="mascotas">
    <div class="container-lg py-5">
      <div class="table-responsive mascotas-tabla-wrap">
        <table class="table mascotas-tabla mb-0">
          <thead>
            <tr>
              <th class="mascotas-titulo" colspan="5" scope="colgroup">Mis mascotas</th>
            </tr>
            <tr>
              <?php
              $columnas = ['Nombre' => 'Nombre', 'Especie' => 'Tipo de animal', 'Raza' => 'Raza', 'FechaNacimiento' => 'Fecha de nacimiento', 'Sexo' => 'Sexo'];
              foreach ($columnas as $col => $etiqueta):
                $flecha = ($orden === $col) ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';
              ?>
                <th scope="col">
                  <a href="?orden=<?= $col ?>&dir=<?= ($orden === $col) ? $dirSiguiente : 'asc' ?>" class="text-decoration-none text-reset">
                    <?= $etiqueta . $flecha ?>
                  </a>
                </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($mascotas)): ?>
              <tr>
                <td colspan="5" class="text-center">No tenés mascotas registradas.</td>
              </tr>
              <?php else: foreach ($mascotas as $m): ?>
                <tr>
                  <td><?= htmlspecialchars($m['Nombre']) ?></td>
                  <td><?= htmlspecialchars($m['Especie']) ?></td>
                  <td><?= htmlspecialchars($m['Raza']) ?></td>
                  <td><?= $m['FechaNacimiento'] ? date('d/m/Y', strtotime($m['FechaNacimiento'])) : '—' ?></td>
                  <td>Masculino</td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>