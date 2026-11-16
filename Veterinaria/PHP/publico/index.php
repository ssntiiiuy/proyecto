<?php
session_start();
$paginaActiva = 'inicio';
$mostrarCarrito = false;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio - Clínica Veterinaria Sienra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../CSS/style.css?v=<?= filemtime(__DIR__ . '/../../CSS/style.css') ?>">
  <script src="../../JS/tema.js"></script>
</head>

<body>
  <?php require __DIR__ . '/../usuario/includes/header.php'; ?>

  <main class="nosotros">
    <section class="nosotros-hero">
      <div class="container-lg">
        <h1 class="nosotros-titulo">Clínica Veterinaria <span>Sienra</span></h1>
        <p class="lead mt-3 mb-0">Cuidamos la salud y el bienestar de tus mascotas.</p>
      </div>
    </section>

    <div class="container-lg pb-5">
      <div class="row g-3">
        <div class="col-md-6">
          <section class="nosotros-card h-100 d-flex flex-column align-items-start">
            <h2><span class="nosotros-icono"><i class="bi bi-bag" aria-hidden="true"></i></span> Productos para tu mascota</h2>
            <p>Explorá los alimentos, accesorios y productos de cuidado disponibles en el catálogo.</p>
            <a class="btn btn-cta w-auto mt-auto px-3 py-2" href="../usuario/catalogo.php">Ver catálogo</a>
          </section>
        </div>
        <div class="col-md-6">
          <section class="nosotros-card h-100 d-flex flex-column align-items-start">
            <h2><span class="nosotros-icono"><i class="bi bi-calendar-week" aria-hidden="true"></i></span> Atención veterinaria</h2>
            <p>Solicitá una cita para tu mascota o consultá los horarios y las vías de contacto de la clínica.</p>
            <div class="d-flex flex-wrap align-items-center gap-3 mt-auto">
              <a class="btn btn-cta w-auto px-3 py-2" href="../usuario/index.php?agendar=1">Agendar una cita</a>
              <a class="nosotros-link text-decoration-underline" href="../usuario/nosotros.php">Horarios y contacto</a>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
