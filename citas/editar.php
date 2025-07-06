<?php
include '../includes/auth.php';
include '../includes/conexion.php';

if (!isset($_GET['id'])) {
    die("ID de cita no recibido.");
}

$id = intval($_GET['id']);
$mensaje = "";
$error = false;
$tipo_mensaje = '';

$stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
$stmt->execute([$id]);
$cita = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cita) die("Cita no encontrada.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST["fecha"];
    $hora = $_POST["hora"];
    $medico = ucwords(strtolower(trim($_POST["medico"])));
    $estado = $_POST["estado"];

    // Validar fecha
    $hoy = date("Y-m-d");
    if ($fecha < $hoy) {
        $mensaje = "La fecha no puede estar en el pasado.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Verificacion y validación de hora
    if (strlen($hora) > 5) {
        $hora = date("H:i", strtotime($hora));
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $mensaje = "Hora inválida.";
        $error = true;
        $tipo_mensaje = 'error';
    } else {
        $hora_valor = strtotime($hora);
        $inicio = strtotime("06:00");
        $fin = strtotime("22:00");

        if ($hora_valor < $inicio || $hora_valor > $fin) {
            $mensaje = "La hora debe estar entre 6:00 A.M. y 10:00 P.M.";
            $error = true;
            $tipo_mensaje = 'error';
        } else {
            if ($fecha === date("Y-m-d")) {
                $fechaHoraCita = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $hora);
                $ahora = new DateTime();

                $fechaHoraCita->setTimezone(new DateTimeZone(date_default_timezone_get()));

                if ($fechaHoraCita <= $ahora) {
                    $mensaje = "No puedes asignar una hora que ya ha pasado.";
                    $error = true;
                    $tipo_mensaje = 'error';
                }
            }
        }
    }

    // Validar médico
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}$/', $medico)) {
        $mensaje = "Nombre del médico inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validar estado
    $estados_validos = ["programada", "completada", "cancelada"];
    if (!in_array($estado, $estados_validos)) {
        $mensaje = "Estado inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Solo actualizar registro si no hay errores
    if (!$error) {
        $sql = "UPDATE citas SET fecha=?, hora=?, medico_asignado=?, estado=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$fecha, $hora, $medico, $estado, $id])) {
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
    <title>Editar Cita</title>
    <link rel="stylesheet" href="../css/editar_cita.css">
</head>

<body>
    <h2>Editar Cita</h2>
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <?php if (!empty($mensaje) && $tipo_mensaje === 'exito'): ?>
        <script>
            setTimeout(() => {
                window.location.href = "../citas/listar.php";
            }, 2000);
        </script>
    <?php endif; ?>

    <form method="post">
        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= $cita['fecha'] ?>" required>

        <label>Hora:</label>
        <input type="time" name="hora" value="<?= date('H:i', strtotime($cita['hora'])) ?>" min="06:00" max="22:00" required>

        <label>Médico asignado:</label>
        <input type="text" name="medico" value="<?= $cita['medico_asignado'] ?>" required>

        <label>Estado:</label>
        <select name="estado" required>
            <option value="programada" <?= $cita['estado'] == 'programada' ? 'selected' : '' ?>>Programada</option>
            <option value="cancelada" <?= $cita['estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
            <option value="completada" <?= $cita['estado'] == 'completada' ? 'selected' : '' ?>>Completada</option>
        </select>

        <button type="submit">Guardar cambios</button>
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