<?php
// cada página setea $tituloPagina antes del include
$tituloPagina = $tituloPagina ?? '';
?>
    <header class="admin-topbar">
      <button class="btn admin-topbar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

      <h1 class="admin-topbar-titulo"><?= $tituloPagina ?></h1>

      <div class="dropdown ms-auto">
        <a class="admin-usuario dropdown-toggle" href="#" id="adminUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <!-- TODO BD: nombre del encargado logueado -->
          <span class="d-none d-sm-inline">Ana Rodríguez</span>
          <span class="icon-badge icon-badge-user"><i class="bi bi-person-fill"></i></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUsuario">
          <li><span class="dropdown-item-text text-muted small">Encargada</span></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item" href="../LoginHTML.php">Cerrar sesión</a></li>
        </ul>
      </div>
    </header>
