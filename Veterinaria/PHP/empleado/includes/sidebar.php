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
/* si la pagina esta en empleado/<rol>/ hay que subir una carpeta mas */
if (in_array(basename(dirname($_SERVER['SCRIPT_FILENAME'])), $mapaRolCarpeta, true)) {
  $rutaEmpleado = '../';
} else {
  $rutaEmpleado = '';
}
$baseRol = $carpetaRol ? "{$rutaEmpleado}{$carpetaRol}" : '';

/* cada rol ve solo lo que le corresponde (RNF4: niveles de acceso segun el rol) */
if ($rol === 'Cajero') {
  $gruposMenu = [
    '' => [
      'dashboard' => ["{$baseRol}/index.php", 'bi-speedometer2', 'Dashboard'],
    ],
    'Caja' => [
      'caja'      => ["{$rutaEmpleado}caja.php", 'bi-cash-coin', 'Nueva venta'],
      'facturas'  => ["{$rutaEmpleado}facturas.php", 'bi-receipt', 'Facturas'],
    ],
    'Clientes' => [
      'usuarios'  => ["{$rutaEmpleado}usuarios.php", 'bi-people', 'Clientes'],
      'mascotas'  => ["{$rutaEmpleado}mascotas.php", 'bi-heart-pulse', 'Mascotas'],
    ],
  ];
} elseif (in_array($rol, ['Veterinario', 'Cirujano', 'Peluquero'], true)) {
  /* personal clinico: segun el documento de RF "los veterinarios acceden a la clinica" (agenda, mascotas, clientes) */
  $gruposMenu = [
    '' => [
      'dashboard' => ["{$baseRol}/index.php", 'bi-speedometer2', 'Dashboard'],
    ],
    'Mi trabajo' => [
      'agenda'    => ["{$rutaEmpleado}citas.php", 'bi-calendar-check', 'Mi agenda'],
      'mascotas'  => ["{$rutaEmpleado}mascotas.php", 'bi-heart-pulse', 'Mascotas'],
      'servicios' => ["{$rutaEmpleado}servicios.php", 'bi-clipboard2-pulse', 'Servicios'],
    ],
    'Clientes' => [
      'usuarios'  => ["{$rutaEmpleado}usuarios.php", 'bi-people', 'Clientes'],
    ],
  ];
} else {
  /* rol desconocido o sesion sin iniciar: solo el dashboard (que redirige al login) */
  $gruposMenu = [
    '' => [
      'dashboard' => [$carpetaRol ? "{$baseRol}/index.php" : "{$rutaEmpleado}../LoginHTML.php", 'bi-speedometer2', 'Dashboard'],
    ],
  ];
}
?>
<div class="admin-sidebar">
  <!-- en pantallas chicas el menú se abre como offcanvas desde la topbar -->
  <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarTitulo">
    <div class="offcanvas-header">
      <span class="admin-brand-text" id="adminSidebarTitulo">Panel Sienra</span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Cerrar menú"></button>
    </div>

    <div class="offcanvas-body flex-column">
      <?php $hrefDashboard = $carpetaRol ? "{$baseRol}/index.php" : "{$rutaEmpleado}../LoginHTML.php"; ?>
      <a class="admin-brand d-none d-lg-flex" href="<?= $hrefDashboard ?>">
        <img src="<?= $rutaEmpleado ?>../../img/logo.png" alt="Logo Clínica Veterinaria Sienra" class="brand-logo" onerror="this.style.display='none'">
        <span class="admin-brand-text">Panel Sienra</span>
      </a>

      <?php if ($rol) { ?>
        <p class="admin-sidebar-rol"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($rol) ?></p>
      <?php } ?>

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
