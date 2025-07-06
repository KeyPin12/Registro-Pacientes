<?php
session_start();
define('INACTIVIDAD_MAXIMA', 900);

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];

    if ($inactivo > INACTIVIDAD_MAXIMA) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?mensaje=sesion_expirada");
        exit;
    }
}

$_SESSION['ultimo_acceso'] = time(); 

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

function es_admin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function es_medico() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'medico';
}

function es_recepcionista() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'recepcionista';
}