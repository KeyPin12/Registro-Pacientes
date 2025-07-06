<?php
include '../includes/auth.php';
include '../includes/conexion.php';

$mensaje = "";
$error = false;
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paciente_id = intval($_POST["paciente_id"]);
    $fecha = $_POST["fecha"];
    $hora = $_POST["hora"];
    $medico = ucwords(strtolower(trim($_POST["medico"])));

    // Validar paciente
    if ($paciente_id <= 0) {
        $mensaje = "Debe seleccionar un paciente válido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validar fecha
    $hoy = date("Y-m-d");
    if ($fecha < $hoy) {
        $mensaje = "La fecha de la cita no puede estar en el pasado.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validar hora
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $mensaje = "Hora inválida.";
        $error = true;
        $tipo_mensaje = 'error';
    } else {
        $hora_valor = strtotime($hora);
        $inicio = strtotime("06:00");
        $fin = strtotime("20:00");

        if ($hora_valor < $inicio || $hora_valor > $fin) {
            $mensaje = "La cita debe estar entre 6:00 A:M y 8:00 P:M.";
            $error = true;
            $tipo_mensaje = 'error';
        }
    }

    // Validar nombre del médico
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}$/', $medico)) {
        $mensaje = "Nombre del médico inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Verificar duplicado de cita
    if (!$error) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM citas WHERE paciente_id = ? AND fecha = ? AND hora = ?");
        $stmt->execute([$paciente_id, $fecha, $hora]);

        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Este paciente ya tiene una cita registrada a esa hora.";
            $error = true;
            $tipo_mensaje = 'error';
        }
    }

    // Insertar en la base de datos si no hay errores
    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO citas (paciente_id, fecha, hora, medico_asignado)
                                VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$paciente_id, $fecha, $hora, $medico])) {
            header("Location: listar.php?mensaje=registrada");
            exit;
        } else {
            header("Location: listar.php?mensaje=error");
            exit;
        }
    }
}

$pacientes = $conn->query("SELECT id, nombre FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Cita</title>
    <link rel="stylesheet" href="../css/agregar_cita.css">
</head>
<body>
    <h2>Agendar Nueva Cita</h2>
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Paciente:</label>
        <select name="paciente_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
            <?php endforeach; ?>
        </select>

        <label>Fecha:</label>
        <input type="date" name="fecha" required>

        <label>Hora:</label>
        <input type="time" name="hora" required>

        <label>Médico asignado:</label>
        <input type="text" name="medico" required>

        <button type="submit">Agendar</button>
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
