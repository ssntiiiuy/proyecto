<?php
/* control de sesion: cada pagina define $rolesPermitidos antes de incluir este archivo */
if (session_status() === PHP_SESSION_NONE) {
  // abro la sesion para poder leer y guardar los datos del usuario
  session_start();
}

/* si la pagina esta en empleado/<rol>/ el login queda dos carpetas arriba */
if (basename(dirname($_SERVER['SCRIPT_FILENAME'], 2)) === 'empleado') {
  $rutaLogin = '../../LoginHTML.php';
} else {
  $rutaLogin = '../LoginHTML.php';
}

// si no hay una cuenta con un rol permitido mando al login y corto la pagina
if (empty($_SESSION['correo']) || !in_array($_SESSION['rol'] ?? null, $rolesPermitidos, true)) {
  header('Location: ' . $rutaLogin);
  exit();
}
