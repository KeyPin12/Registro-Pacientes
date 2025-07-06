<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$tipo_mensaje = "";
$error = false;

if (!isset($_GET['id'])) {
    header("Location: listar.php?mensaje=errorid");
    exit;
}

$id = $_GET['id'];

// Consultar ficha médica
$stmt = $conn->prepare("
    SELECT f.*, p.nombre 
    FROM fichas_medicas f 
    JOIN pacientes p ON f.paciente_id = p.id 
    WHERE f.id = ?
");
$stmt->execute([$id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ficha) die("Ficha médica no encontrada.");

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $motivo = trim($_POST["motivo"]);
    $diagnostico = trim($_POST["diagnostico"]);
    $tratamiento = trim($_POST["tratamiento"]);
    $observaciones = trim($_POST["observaciones"]);

    if (empty($motivo) || empty($diagnostico) || empty($tratamiento)) {
        $mensaje = "Todos los campos son obligatorios excepto observaciones.";
        $tipo_mensaje = "error";
        $error = true;
    }

    if (!$error) {
        $sql = "UPDATE fichas_medicas SET motivo=?, diagnostico=?, tratamiento=?, observaciones=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$motivo, $diagnostico, $tratamiento, $observaciones, $id])) {
            $tipo_mensaje = "exito";
            header("Location: listar.php?mensaje=actualizada");
            exit;
        } else {
            header("Location: listar.php?mensaje=error");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Ficha Médica</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/editar_cita.css">
</head>

<body>

    <h2>Editar Ficha Médica - <?= htmlspecialchars($ficha['nombre']) ?></h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Motivo:</label>
        <textarea name="motivo" required><?= htmlspecialchars($ficha['motivo']) ?></textarea>

        <label>Diagnóstico:</label>
        <textarea name="diagnostico" required><?= htmlspecialchars($ficha['diagnostico']) ?></textarea>

        <label>Tratamiento:</label>
        <textarea name="tratamiento" required><?= htmlspecialchars($ficha['tratamiento']) ?></textarea>

        <label>Observaciones:</label>
        <textarea name="observaciones"><?= htmlspecialchars($ficha['observaciones']) ?></textarea>

        <button type="submit">Guardar cambios</button>
        <a href="listar.php" class="btn-volver">← Volver</a>
    </form>

    <?php if (!empty($mensaje) && $tipo_mensaje === 'exito'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../fichas_medicas/listar.php";
            }, 2000);
        </script>
    <?php endif; ?>

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