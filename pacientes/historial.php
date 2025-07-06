<?php
include '../includes/auth.php';
include '../includes/conexion.php';

if (!isset($_GET['id'])) {
    die("ID del paciente no proporcionado.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado.");
}

$stmt = $conn->prepare("SELECT * FROM consultas WHERE paciente_id = ? ORDER BY fecha DESC");
$stmt->execute([$id]);
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial Clínico de <?= htmlspecialchars($paciente['nombre']) ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/historial.css">
</head>

<body>
    <div class="info-paciente">
        <p><strong>Paciente:</strong> <?= $paciente['nombre'] ?></p>
        <p><strong>Cédula:</strong> <?= $paciente['cedula'] ?> | <strong>Edad:</strong> <?= $paciente['edad'] ?> años</p>
    </div>

    <h3>Historial de consultas:</h3>

    <?php foreach ($consultas as $c): ?>
        <div class="card-consulta">
            <div class="fecha">🕒 <?= date("d/m/Y H:i", strtotime($c['fecha'])) ?></div>
            <p><strong>Motivo:</strong> <?= nl2br(htmlspecialchars($c['motivo'])) ?></p>
            <p><strong>Diagnóstico:</strong> <?= nl2br(htmlspecialchars($c['diagnostico'])) ?></p>
            <p><strong>Tratamiento:</strong> <?= nl2br(htmlspecialchars($c['tratamiento'])) ?></p>
        </div>
    <?php endforeach; ?>

    <a href="listar.php" class="btn-volver">← Volver</a>

    <script>
        const TIEMPO_MAX_INACTIVIDAD = 15 * 60 * 1000;
        let temporizador;

        function cerrarSesion() {
            window.location.href = '../logout.php?mensaje=sesion_expirada';
        }

        function reiniciarTemporizador() {
            clearTimeout(temporizador);
            temporizador = setTimeout(cerrarSesion, TIEMPO_MAX_INACTIVIDAD);
        }
        window.onload = reiniciarTemporizador;
        document.onmousemove = reiniciarTemporizador;
        document.onkeydown = reiniciarTemporizador;
        document.onclick = reiniciarTemporizador;
        document.onscroll = reiniciarTemporizador;
    </script>
</body>

</html>