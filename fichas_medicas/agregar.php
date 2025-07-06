<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$tipo_mensaje = "";
$error = false;

// Obtener lista de pacientes
$pacientes = $conn->query("SELECT id, nombre FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $paciente_id = $_POST["paciente_id"];
    $motivo = trim($_POST["motivo"]);
    $diagnostico = trim($_POST["diagnostico"]);
    $tratamiento = trim($_POST["tratamiento"]);
    $observaciones = trim($_POST["observaciones"]);

    // Validación básica
    if (empty($motivo) || empty($diagnostico) || empty($tratamiento)) {
        $mensaje = "Todos los campos son obligatorios excepto observaciones.";
        $tipo_mensaje = "error";
        $error = true;
    }

    if (!$error) {
        $sql = "INSERT INTO fichas_medicas (paciente_id, motivo, diagnostico, tratamiento, observaciones)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$paciente_id, $motivo, $diagnostico, $tratamiento, $observaciones])) {
            header("Location: listar.php?mensaje=registrada");
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
    <title>Registrar Ficha Médica</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/agregar_cita.css">
</head>

<body>

    <h2>Registrar Ficha Médica</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if (!empty($mensaje) && $tipo_mensaje === 'exito'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../fichas_medicas/listar.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <form method="post">
        <label>Paciente:</label>
        <select name="paciente_id" required>
            <option value="">-- Seleccionar --</option>
            <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Motivo:</label>
        <textarea name="motivo" value="<?= htmlspecialchars($motivo ?? '') ?>" required></textarea>

        <label>Diagnóstico:</label>
        <textarea name="diagnostico" value="<?= htmlspecialchars($diagnostico ?? '') ?>" required></textarea>

        <label>Tratamiento:</label>
        <textarea name="tratamiento" value="<?= htmlspecialchars($tratamiento ?? '') ?>" required></textarea>

        <label>Observaciones:</label>
        <textarea name="observaciones" value="<?= htmlspecialchars($observaciones ?? '') ?>"></textarea>

        <button type="submit">Guardar ficha</button>
        <a href="listar.php" class="btn-volver">← Volver</a>
    </form>

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