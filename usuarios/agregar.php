<?php
include '../includes/auth.php';
if (!es_admin()) die("Acceso restringido.");

include '../includes/conexion.php';
// Variables para los mensajes
$mensaje = "";
$error = false;
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $clave = $_POST['clave'];
    $rol = $_POST['rol'];

    // Validación de usuario
    if (strlen($usuario) < 4 || strlen($usuario) > 50) {
        $mensaje = "El nombre de usuario debe tener entre 4 y 50 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    } else {
        // Verificar si ya existe ese usuario
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "El usuario ya existe.";
            $error = true;
            $tipo_mensaje = 'error';
        }
    }

    // Validación de contraseña
    if (strlen($clave) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de rol
    $roles_validos = ['admin', 'medico', 'recepcionista'];
    if (!in_array($rol, $roles_validos)) {
        $mensaje = "Rol inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Si no hay errores, registrar en la base de datos
    if (!$error) {
        $hash = password_hash($clave, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contraseña_hash, rol) VALUES (?, ?, ?)");
        if ($stmt->execute([$usuario, $hash, $rol])) {
            $mensaje = "Usuario creado con éxito.";
            header("Location: listar.php?mensaje=registrado");
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
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/agregar_usuario.css">
</head>
<body>
    <h2>Crear Nuevo Usuario</h2>
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nombre de usuario:</label>
        <input type="text" name="usuario" value="<?= htmlspecialchars($usuario ?? '') ?>" required>

        <label>Contraseña:</label>
        <input type="password" name="clave" value="<?= htmlspecialchars($clave ?? '') ?>" required>

        <label>Rol:</label>
        <select name="rol" value="<?= htmlspecialchars($rol ?? '') ?>" required>
            <option value="admin">Administrador</option>
            <option value="medico">Médico</option>
            <option value="recepcionista">Recepcionista</option>
        </select>

        <button type="submit">Crear Usuario</button>
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
