<?php
// cada página setea $paginaActiva antes del include
$paginaActiva = $paginaActiva ?? '';

require_once __DIR__ . '/../../clases/Conexion.php';
require_once __DIR__ . '/../../clases/Usuario.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$rol = $_SESSION['rol'] ?? null;

$mapaRolCarpeta = [
  'Cajero'      => 'cajero',
  'Cirujano'    => 'cirujano',
  'Peluquero'   => 'peluquero',
  'Veterinario' => 'veterinario',
];

$carpetaRol = $mapaRolCarpeta[$rol] ?? '';

$gruposMenu = [
  '' => [
    'dashboard' => [$carpetaRol ? "/PHP/empleado/{$carpetaRol}/index.php" : '/PHP/login/LoginHTML.php', 'bi-speedometer2', 'Dashboard'],
  ],
  'Gestión' => [
    'citas'     => ["/PHP/empleado/{$carpetaRol}/citas.php", 'bi-calendar-check', 'Citas'],
    'usuarios'  => ['/PHP/empleado/usuarios.php', 'bi-people', 'Usuarios'],
    'mascotas'  => ['/PHP/empleado/mascotas.php', 'bi-heart-pulse', 'Mascotas'],
    'servicios' => ['/PHP/empleado/servicios.php', 'bi-clipboard2-pulse', 'Servicios'],
  ],
  'Inventario y ventas' => [
    'productos'   => ['/PHP/empleado/productos.php', 'bi-box-seam', 'Productos'],
    'proveedores' => ['/PHP/empleado/proveedores.php', 'bi-truck', 'Proveedores'],
    'facturas'    => ['/PHP/empleado/facturas.php', 'bi-receipt', 'Facturas'],
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
      <?php $hrefDashboard = $carpetaRol ? "/PHP/empleado/{$carpetaRol}/index.php" : '/PHP/login/LoginHTML.php'; ?>
      <a class="admin-brand d-none d-lg-flex" href="<?= $hrefDashboard ?>">
        <img src="/PHP/img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
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