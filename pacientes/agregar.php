<?php
include '../includes/auth.php';
include '../includes/conexion.php';
// Variables para los mensajes
$mensaje = "";
$error = false;
$tipo_mensaje = '';

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
        $stmt = $conn->prepare("SELECT COUNT(*) FROM pacientes WHERE cedula = ?");
        $stmt->execute([$cedula]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Ya existe un paciente con esta cédula.";
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

    // Solo insertar en la base de datos si no hay errores
    if (!$error) {
        try {
            $sql = "INSERT INTO pacientes (nombre, cedula, edad, sexo, direccion, contacto)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nombre, $cedula, $edad, $sexo, $direccion, $contacto]);
            header("Location: listar.php?mensaje=registrado");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensaje = "La cédula ya está registrada.";
                $tipo_mensaje = 'error';
            } else {
                $mensaje = "Error inesperado al registrar el paciente.";
                $tipo_mensaje = 'error';
                header("Location: listar.php?mensaje=error");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Paciente</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/agregar.css">
</head>

<body>
    <h2>Registrar Nuevo Paciente</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Nombre completo:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre ?? '') ?>" required>

        <label>Cédula:</label>
        <input type="text" name="cedula" value="<?= htmlspecialchars($cedula ?? '') ?>" required>

        <label>Edad:</label>
        <input type="number" name="edad" value="<?= htmlspecialchars($edad ?? '') ?>" required>

        <label>Sexo:</label>
        <select name="sexo" value="<?= htmlspecialchars($sexo ?? '') ?>" required>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
        </select>

        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($direccion ?? '') ?>">

        <label>Contacto:</label>
        <input type="tel" name="contacto" pattern="[0-9]{7,15}" maxlength="15" value="<?= htmlspecialchars($contacto ?? '') ?>" required>

        <button type="submit">Registrar</button>
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