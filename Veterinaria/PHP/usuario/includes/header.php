<?php
// cada página setea $paginaActiva y $mostrarCarrito antes de hacer el include
$paginaActiva = $paginaActiva ?? '';
$mostrarCarrito = $mostrarCarrito ?? true;

$enlacesNav = [
  'catalogo' => ['../usuario/catalogo.php', 'Catálogo'],
  'mascotas' => ['../usuario/mascotas.php', 'Mascotas'],
  'citas'    => ['../usuario/index.php', 'Citas'],
  'nosotros' => ['../usuario/nosotros.php', 'Nosotros'],
];

$nombreUsuario = $nombreUsuario ?? ($_SESSION['nombre'] ?? 'Usuario');
?>
<header class="site-header" data-bs-theme="light">
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-3 px-lg-4">

      <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
        <img src="/img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
        <span class="brand-text">Clínica<br>Veterinaria<br>SIENRA</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav mx-auto gap-lg-2">
          <?php foreach ($enlacesNav as $clave => [$href, $texto]) { ?>
            <?php if ($clave === $paginaActiva) { ?>
              <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= $href ?>"><?= $texto ?></a></li>
            <?php } else { ?>
              <li class="nav-item"><a class="nav-link" href="<?= $href ?>"><?= $texto ?></a></li>
            <?php } ?>
          <?php } ?>
        </ul>

        <ul class="navbar-nav ms-lg-3 align-items-center gap-2">
          <li class="nav-item">
            <button class="nav-link icon-badge" type="button" data-tema-toggle aria-label="Cambiar entre tema claro y oscuro">
              <i class="bi bi-sun-fill tema-icono-sol"></i>
              <i class="bi bi-moon-fill tema-icono-luna"></i>
            </button>
          </li>
          <?php if ($mostrarCarrito) { ?>
            <li class="nav-item">
              <a class="nav-link icon-badge" href="#" aria-label="Carrito">
                <i class="bi bi-cart3"></i>
              </a>
            </li>
          <?php } ?>
          <li class="nav-item dropdown">
            <a class="nav-link icon-badge icon-badge-user" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Usuario">
              <i class="bi bi-person-fill"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
              <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($nombreUsuario) ?></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Mi perfil</a></li>
              <li>
                <a class="dropdown-item" href="../LoginHTML.php" id="btnCerrarSesion" data-bs-toggle="modal" data-bs-target="#modalCerrarSesion">
                  Cerrar sesión
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>

<!-- Modal de confirmación para cerrar sesión -->
<div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCerrarSesionLabel">Cerrar sesión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas cerrar sesión?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="../LoginHTML.php" class="btn btn-danger">Sí, cerrar sesión</a>
      </div>
    </div>
  </div>
</div>