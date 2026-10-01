<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros - Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

  <?php
  $paginaActiva = 'nosotros';
  $mostrarCarrito = true;
  include __DIR__ . '/includes/header.php';
  ?>

  <main class="nosotros">
    <div class="container-lg py-4">

      <h1 class="nosotros-titulo">Clínica Veterinaria Sienra</h1>

      <div class="row g-3">

        <div class="col-lg-7 d-flex flex-column gap-3">

          <section class="nosotros-card">
            <h2>Sobre nosotros</h2>
            <p>En Clínica Veterinaria Sienra, nuestro compromiso es el bienestar integral de tus compañeros más fieles.</p>
            <p class="mb-0">Desde hace años, brindamos atención médica de calidad, combinando profesionalismo con el cariño que cada mascota merece. Tu tranquilidad y la salud de tu mejor amigo son nuestra prioridad diaria.</p>
          </section>

          <div class="row g-3">
            <div class="col-sm-6">
              <section class="nosotros-card h-100">
                <h2>Horarios</h2>
                <p class="nosotros-horario mb-0">
                  <u>Lunes a Viernes:</u> 08:30 a 12:30 hrs y 15:00 a 19:00 hrs.<br>
                  <u>Sábados:</u> 09:00 a 13:00 hrs.
                </p>
              </section>
            </div>
            <div class="col-sm-6">
              <section class="nosotros-card h-100">
                <h2>Contacto</h2>
                <p class="mb-0"><a class="nosotros-link" href="tel:092662279">092 662 279</a></p>
              </section>
            </div>
          </div>

        </div>

        <div class="col-lg-5">
          <section class="nosotros-card h-100">
            <h2><i class="bi bi-cursor-fill nosotros-icono"></i> Ubicación</h2>
            <!-- mapa embebido, no precisa API key -->
            <div class="ratio ratio-4x3 nosotros-mapa">
              <iframe src="https://maps.google.com/maps?q=Rom%C3%A1n%20Guerra%20721%2C%20Maldonado%2C%20Uruguay&output=embed" title="Mapa de la Clínica Veterinaria Sienra" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <p class="mt-3 mb-0">Nos encontramos en Román Guerra 721, 20000 Maldonado, Departamento de Maldonado.</p>
          </section>
        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
