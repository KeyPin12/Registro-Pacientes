<?php
include '../includes/auth.php';
if (!es_admin()) die("Acceso restringido.");

include '../includes/conexion.php';

if (!isset($_GET['id'])) {
    header("Location: listar.php?mensaje=error");
    exit;
}

$id = intval($_GET['id']);
if ($_SESSION['usuario_id'] == $id) {
    header("Location: listar.php?mensaje=error");
    exit;
}

// Verificar si el usuario existe en la base de datos
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header("Location: listar.php?mensaje=errorexiste");
    exit;
}

// Eliminar si no hay algún error
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: listar.php?mensaje=eliminado");
    exit;
} else {
    header("Location: listar.php?mensaje=error");
    exit;
}
