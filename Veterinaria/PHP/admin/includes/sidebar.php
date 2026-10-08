<?php
// cada página setea $paginaActiva antes del include
$paginaActiva = $paginaActiva ?? '';

$gruposMenu = [
  '' => [
    'dashboard' => ['index.php', 'bi-speedometer2', 'Dashboard'],
  ],
  'Gestión' => [
    'citas'     => ['citas.php', 'bi-calendar-check', 'Citas'],
    'usuarios'  => ['usuarios.php', 'bi-people', 'Usuarios'],
    'mascotas'  => ['mascotas.php', 'bi-heart-pulse', 'Mascotas'],
    'servicios' => ['servicios.php', 'bi-clipboard2-pulse', 'Servicios'],
  ],
  'Inventario y ventas' => [
    'productos'   => ['productos.php', 'bi-box-seam', 'Productos'],
    'proveedores' => ['proveedores.php', 'bi-truck', 'Proveedores'],
    'facturas'    => ['facturas.php', 'bi-receipt', 'Facturas'],
  ],
];
?>
  <div class="admin-sidebar">
    <!-- en pantallas chicas el menú se abre como offcanvas desde la topbar -->
    <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarTitulo">
      <div class="offcanvas-header">
        <span class="admin-brand-text" id="adminSidebarTitulo">Panel Sienra</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Cerrar menú"></button>
      </div>

      <div class="offcanvas-body flex-column">
        <a class="admin-brand d-none d-lg-flex" href="index.php">
          <img src="../img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
          <span class="admin-brand-text">Panel Sienra</span>
        </a>

<?php foreach ($gruposMenu as $grupo => $items) { ?>
        <hr class="admin-sidebar-divider">
<?php if ($grupo !== '') { ?>
        <p class="admin-sidebar-heading"><?= $grupo ?></p>
<?php } ?>
        <ul class="nav flex-column">
<?php foreach ($items as $clave => [$href, $icono, $texto]) { ?>
<?php if ($clave === $paginaActiva) { ?>
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= $href ?>"><i class="bi <?= $icono ?>"></i> <?= $texto ?></a></li>
<?php } else { ?>
          <li class="nav-item"><a class="nav-link" href="<?= $href ?>"><i class="bi <?= $icono ?>"></i> <?= $texto ?></a></li>
<?php } ?>
<?php } ?>
        </ul>
<?php } ?>
      </div>
    </div>
  </div>
