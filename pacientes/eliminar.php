<?php
include '../includes/auth.php';
include '../includes/conexion.php';

if (!isset($_GET['id'])) {
    die('ID de paciente no proporcionado.');
}

$id = intval($_GET['id']);

// Verificar que exite el paciente
$stmt = $conn->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    header("Location: listar.php?mensaje=errorpaciente");
    exit;
}

// Verificar si tiene consultas el paciente
$stmt = $conn->prepare("SELECT COUNT(*) FROM consultas WHERE paciente_id = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    header("Location: listar.php?mensaje=errorconsultas");
    exit;
}

// Verificar si tiene citas
$stmt = $conn->prepare("SELECT COUNT(*) FROM citas WHERE paciente_id = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    header("Location: listar.php?mensaje=errorcitas");
    exit;
}

// Eliminar si no hay ningún error
$stmt = $conn->prepare("DELETE FROM pacientes WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: listar.php?mensaje=eliminado");
    exit;
} else {
    header("Location: listar.php?mensaje=error");
    exit;
}
?>
