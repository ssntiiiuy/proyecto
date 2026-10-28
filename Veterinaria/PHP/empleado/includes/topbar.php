<?php

/* iniciar sesion si no esta iniciada */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../clases/Conexion.php';

$nombreUsuario = 'Usuario';
$conexion = (new Conexion())->establecerConexion();

try {
  if (!empty($_SESSION['correo'])) {
    $conexion = (new Conexion())->establecerConexion();

    $sql = "SELECT usuario.Nombre, usuario.Apellido FROM usuario INNER JOIN HACE ON usuario.CI = HACE.CI WHERE HACE.Correo = :correo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':correo' => $_SESSION['correo']]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
      $nombreUsuario = $usuario['Nombre' ] . ' ' . $usuario['Apellido'];
    }
  }

    $sqlRol = "SELECT login.tipoUsuario, e.rol
        FROM login
        INNER JOIN hace h ON login.Correo = h.Correo
        INNER JOIN empleado e ON h.CI = e.CI
        WHERE login.Correo = :correo";
    $stmtRol = $conexion->prepare($sqlRol);
    $stmtRol->execute([':correo' => $_SESSION['correo']]);
    $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
  echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red; margin:10px;'>";
  echo "<b>Error SQL en topbar.php:</b> " . $e->getMessage();
  echo "</div>";
}
?>
<header class="admin-topbar" data-bs-theme="light">
  <button class="btn admin-topbar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Abrir menú">
    <i class="bi bi-list"></i>
  </button>

  <h1 class="admin-topbar-titulo"><?= $tituloPagina ?></h1>

  <button class="btn admin-topbar-toggle ms-auto" type="button" data-tema-toggle aria-label="Cambiar entre tema claro y oscuro">
    <i class="bi bi-sun-fill tema-icono-sol"></i>
    <i class="bi bi-moon-fill tema-icono-luna"></i>
  </button>

  <div class="dropdown">
    <a class="admin-usuario dropdown-toggle" href="#" id="adminUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false">
       <span class="d-none d-sm-inline"><?= htmlspecialchars($nombreUsuario) ?></span>
      <span class="icon-badge icon-badge-user"><i class="bi bi-person-fill"></i></span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUsuario">
      <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($rol['rol']) ?></span></li>
      <li>
        <hr class="dropdown-divider">
      </li>
      <li>
        <a class="dropdown-item" href="/../../PHP/LoginHTML.php" id="btnCerrarSesionAdmin" data-bs-toggle="modal" data-bs-target="#modalCerrarSesionAdmin">
          Cerrar sesión
        </a>
      </li>
    </ul>
  </div>
</header>

<!-- Modal de confirmación para cerrar sesión -->
<div class="modal fade" id="modalCerrarSesionAdmin" tabindex="-1" aria-labelledby="modalCerrarSesionAdminLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCerrarSesionAdminLabel">Cerrar sesión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas cerrar sesión?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <a href="/../../PHP/LoginHTML.php" class="btn btn-danger">Sí, cerrar sesión</a>
      </div>
    </div>
  </div>
</div>