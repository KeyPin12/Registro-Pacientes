<?php
include '../includes/auth.php';
include '../includes/conexion.php';

if (!isset($_GET['id'])) {
    die('ID de paciente no proporcionado.');
}

$id = intval($_GET['id']);
$mensaje = "";
$error = false;
$tipo_mensaje = '';

$stmt = $conn->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die('Paciente no encontrado.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = ucwords(strtolower(trim($_POST["nombre"])));
    $cedula = trim($_POST["cedula"]);
    $edad = intval($_POST["edad"]);
    $sexo = $_POST["sexo"];
    $direccion = trim($_POST["direccion"]);
    $contacto = trim($_POST["contacto"]);

    // Validación de nombre
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,100}$/', $nombre)) {
        $mensaje = "El nombre solo debe contener letras y espacios.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de cédula
    if (!preg_match('/^[0-9]{5,20}$/', $cedula)) {
        $mensaje = "La cédula debe contener solo números.";
        $error = true;
        $tipo_mensaje = 'error';
    } else {
        // Verificar duplicado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM pacientes WHERE cedula = ? AND id != ?");
        $stmt->execute([$cedula, $id]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Lo sentimos, ya existe otro paciente con esta cédula.";
            $error = true;
            $tipo_mensaje = 'error';
        }
    }

    // Validación de edad
    if ($edad < 0 || $edad > 120) {
        $mensaje = "La edad debe estar entre 0 y 120.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de sexo
    $opciones_validas = ["Masculino", "Femenino", "Otro"];
    if (!in_array($sexo, $opciones_validas)) {
        $mensaje = "Sexo inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de dirección
    if (!empty($direccion) && strlen($direccion) < 5) {
        $mensaje = "La dirección es muy corta debe tener mínimo 5 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de contacto
    if (!preg_match('/^[0-9]{7,15}$/', $contacto)) {
        $mensaje = "El número de contacto debe tener entre 7 y 15 dígitos.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Si todo está bien, actualizar
    if (!$error) {
        $sql = "UPDATE pacientes SET nombre=?, cedula=?, edad=?, sexo=?, direccion=?, contacto=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$nombre, $cedula, $edad, $sexo, $direccion, $contacto, $id])) {
            header("Location: listar.php?mensaje=actualizado");
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
    <title>Editar Paciente</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/editar_paciente.css">
</head>
<body>
    <h2>Editar Paciente</h2>
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($paciente['nombre']) ?>" required>

        <label>Cédula:</label>
        <input type="text" name="cedula" value="<?= $paciente['cedula'] ?>" required>

        <label>Edad:</label>
        <input type="number" name="edad" value="<?= $paciente['edad'] ?>" required>

        <label>Sexo:</label>
        <select name="sexo" required>
            <option value="Masculino" <?= $paciente['sexo'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
            <option value="Femenino" <?= $paciente['sexo'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
            <option value="Otro" <?= $paciente['sexo'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>

        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($paciente['direccion']) ?>">

        <label>Contacto:</label>
        <input type="text" name="contacto" value="<?= $paciente['contacto'] ?>">

        <button type="submit">Actualizar</button>
        <a href="listar.php">← Volver</a>
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
