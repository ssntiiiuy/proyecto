<?php
// abro la sesion para poder leer y guardar los datos del usuario
session_start();
// borro la sesion del servidor para cerrar el acceso del usuario
session_destroy();

header('Location: LoginHTML.php');
exit();
