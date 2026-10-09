<?php
session_start();

require_once "Conexion.php";

$nombreUsuario = "Usuario";

try {
    $conexion = (new Conexion())->establecerConexion();

    $sql = "SELECT nombre FROM usuario WHERE correo_login = :correo"; // esto hay que cambiarlo por las nuevas tablas
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':correo' => $_SESSION['correo']]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && !empty($usuario['nombre'])) {
        $nombreUsuario = $usuario['nombre'];
    }
} catch (PDOException $e) {
    echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red; margin:10px;'>";
    echo "<b>Error SQL en index.php:</b> " . $e->getMessage();
    echo "</div>";
}
?>

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

  <header class="site-header">
    <nav class="navbar navbar-expand-lg">
      <div class="container-fluid px-3 px-lg-4">

        <a class="navbar-brand d-flex align-items-center gap-2" href="../PHP/index.php">
          <img src="img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
          <span class="brand-text">Clínica<br>Veterinaria<br>SIENRA</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
          <ul class="navbar-nav mx-auto gap-lg-2">
            <li class="nav-item"><a class="nav-link" href="catalogo.php">Catálogo</a></li>
            <li class="nav-item"><a class="nav-link" href="mascotas.php">Mascotas</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php">Citas</a></li>
            <li class="nav-item"><a class="nav-link" href="nosotros.php">Nosotros</a></li>
          </ul>

          <ul class="navbar-nav ms-lg-3 align-items-center gap-2">
            <li class="nav-item">
              <a class="nav-link icon-badge" href="#" aria-label="Carrito">
                <i class="bi bi-cart3"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link icon-badge icon-badge-user" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Usuario">
                <i class="bi bi-person-fill"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($nombreUsuario) ?></span></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="#">Mi perfil</a></li>
                <li><a class="dropdown-item" href="LoginHTML.php">Cerrar sesión</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="dashboard">
    <div class="container-lg py-4">

      <div class="greeting-card">
        <h1>Hola, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
      </div>

      <div class="alert-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-circle"></i>
          <span><strong>Vacuna rabia de Rex</strong> vence 20/07/2026</span>
        </div>
        <button class="btn btn-outline-light btn-sm px-3" type="button">Pedir cita</button>
      </div>

      <div class="row g-3 mt-1">

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
</body>

</html>