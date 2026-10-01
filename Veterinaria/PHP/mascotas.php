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

  <link rel="stylesheet" href="../CSS/style.css">
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
              <th scope="col">Nombre</th>
              <th scope="col">Tipo de animal</th>
              <th scope="col">Raza</th>
              <th scope="col">Fecha de nacimiento</th>
              <th scope="col">Sexo</th>
            </tr>
          </thead>
          <!-- TODO BD: mascotas del usuario -->
          <tbody>
            <tr>
              <td>Rex</td>
              <td>Perro</td>
              <td>Labrador</td>
              <td>05/08/2015</td>
              <td>Macho</td>
            </tr>
            <tr>
              <td>Luna</td>
              <td>Gato</td>
              <td>Siamés</td>
              <td>12/03/2021</td>
              <td>Hembra</td>
            </tr>
            <tr>
              <td>Toby</td>
              <td>Perro</td>
              <td>Caniche</td>
              <td>20/11/2019</td>
              <td>Macho</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
