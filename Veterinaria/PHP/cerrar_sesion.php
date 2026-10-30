<?php
session_start();
session_destroy();

header('Location: LoginHTML.php');
exit();
