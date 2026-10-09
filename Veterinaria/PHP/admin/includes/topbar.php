<?php
// cada página setea $tituloPagina antes del include
$tituloPagina = $tituloPagina ?? '';

session_start();
require_once __DIR__ . '/../../Conexion.php';
try {
  if (!empty($_SESSION['correo'])) {
    $conexion = (new Conexion())->establecerConexion();

    $sql = "SELECT usuario.Nombre, usuario.Apellido FROM usuario INNER JOIN HACE ON usuario.CI = hace.CI WHERE hace.Correo = :correo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':correo' => $_SESSION['correo']]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
      $nombreUsuario = $usuario['Nombre' ] . ' ' . $usuario['Apellido'];
    }
  }
} catch (PDOException $e) {
  echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red; margin:10px;'>";
  echo "<b>Error SQL en topbar.php:</b> " . $e->getMessage();
  echo "</div>";
}
?>
<header class="admin-topbar">
  <button class="btn admin-topbar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Abrir menú">
    <i class="bi bi-list"></i>
  </button>

  <h1 class="admin-topbar-titulo"><?= $tituloPagina ?></h1>

  <div class="dropdown ms-auto">
    <a class="admin-usuario dropdown-toggle" href="#" id="adminUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
       <span class="d-none d-sm-inline"><?= htmlspecialchars($nombreUsuario) ?></span>
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