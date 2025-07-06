<?php
include '../includes/auth.php';
include '../includes/conexion.php';

if (!es_admin()) {
    die("Acceso restringido: solo administradores pueden eliminar fichas médicas.");
}

if (!isset($_GET['id'])) {
    header("Location: listar.php?mensaje=errorid");
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM fichas_medicas WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: listar.php?mensaje=eliminada");
    exit;
} else {
    header("Location: listar.php?mensaje=error");
    exit;
}