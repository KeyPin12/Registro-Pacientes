<?php
session_start();
$_SESSION = [];
session_destroy();
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
header("Location: index.php?mensaje=$mensaje");
exit;