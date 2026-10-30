<?php
/* control de sesion: cada pagina define $rolesPermitidos antes de incluir este archivo */
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* si la pagina esta en empleado/<rol>/ el login queda dos carpetas arriba */
if (basename(dirname($_SERVER['SCRIPT_FILENAME'], 2)) === 'empleado') {
  $rutaLogin = '../../LoginHTML.php';
} else {
  $rutaLogin = '../LoginHTML.php';
}

if (empty($_SESSION['correo']) || !in_array($_SESSION['rol'] ?? null, $rolesPermitidos, true)) {
  header('Location: ' . $rutaLogin);
  exit();
}
