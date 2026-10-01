<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clínica Veterinaria Sienra</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../CSS/style.css">
</head>

<body>

<?php
  $paginaActiva = 'citas';
  $mostrarCarrito = true;
  include __DIR__ . '/includes/header.php';
  ?>

  <!-- ===================== CONTENIDO ===================== -->
  <main class="dashboard">
    <div class="container-lg py-4">

      <div class="greeting-card">
        <h1>Hola, Jorge!</h1>
      </div>

      <div class="alert-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-circle"></i>
          <span><strong>Vacuna rabia de Rex</strong> vence 20/07/2026</span>
        </div>
        <button class="btn btn-outline-light btn-sm px-3" type="button">Pedir cita</button>
      </div>

      <div class="row g-3 mt-1">

        <!-- Columna izquierda -->
        <div class="col-lg-6 d-flex flex-column gap-3">

          <div class="panel-card">
            <div class="panel-header">
              <h2>Mis mascotas</h2>
              <span class="badge badge-info">2 total</span>
            </div>

            <div class="pet-row">
              <div>
                <p class="pet-name">Rex</p>
                <p class="pet-meta">Labrador - Macho - 9 años</p>
              </div>
              <span class="badge badge-danger"><i class="bi bi-exclamation-circle-fill"></i> Vacuna</span>
            </div>
            <hr class="divider">
            <div class="pet-row">
              <div>
                <p class="pet-name">Luna</p>
                <p class="pet-meta">Siamés - Hembra - 5 años</p>
              </div>
              <span class="badge badge-success">Al día</span>
            </div>
          </div>

          <div class="panel-card">
            <div class="panel-header">
              <h2>Mis turnos</h2>
            </div>
            <div class="pet-row">
              <div>
                <p class="pet-name">Luna</p>
                <p class="pet-meta">Peluquería - 02/07/2026 - 10:00</p>
              </div>
              <span class="badge badge-success">Confirmado</span>
            </div>
          </div>

        </div>

        <!-- Columna derecha -->
        <div class="col-lg-6 d-flex flex-column gap-3">

          <div class="panel-card flex-grow-1">
            <div class="panel-header">
              <h2>Carnet de vacunas</h2>
            </div>

            <p class="group-label">Rex</p>
            <div class="pet-row">
              <span class="pet-meta">Parvovirus</span>
              <span class="badge badge-success">Vence: Nov. 2026</span>
            </div>
            <hr class="divider">
            <div class="pet-row">
              <span class="pet-meta">Rabia</span>
              <span class="badge badge-danger">Vence: 15/07</span>
            </div>

            <p class="group-label mt-3">Luna</p>
            <div class="pet-row">
              <span class="pet-meta">Parvovirus</span>
              <span class="badge badge-success">Vence: Dic. 2026</span>
            </div>
            <hr class="divider">
            <div class="pet-row">
              <span class="pet-meta">FeLV</span>
              <span class="badge badge-success">Vence: Feb. 2027</span>
            </div>
          </div>

          <button class="btn btn-cta" type="button">Solicitar nuevo turno</button>

        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>