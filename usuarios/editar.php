<?php
include '../includes/auth.php';
if (!es_admin()) die("Acceso restringido.");
include '../includes/conexion.php';

if (!isset($_GET['id'])) die("ID no recibido.");

$id = intval($_GET['id']);
$mensaje = "";
$error = false;
$tipo_mensaje = '';

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario_actual = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario_actual) die("Usuario no encontrado.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $rol = $_POST['rol'];
    $nueva_clave = $_POST['clave'];

    // Validación de nombre
    if (strlen($usuario) < 4 || strlen($usuario) > 50) {
        $mensaje = "El nombre de usuario debe tener entre 4 y 50 caracteres.";
        $error = true;
        $tipo_mensaje = 'error';
    } else {
        // Verificar si hay duplicado
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id != ?");
        $stmt->execute([$usuario, $id]);
        if ($stmt->fetchColumn() > 0) {
            $mensaje = "Ya existe otro usuario con ese nombre.";
            $error = true;
            $tipo_mensaje = 'error';
        }
    }

    // Validación de rol
    $roles_validos = ['admin', 'medico', 'recepcionista'];
    if (!in_array($rol, $roles_validos)) {
        $mensaje = "Rol inválido.";
        $error = true;
        $tipo_mensaje = 'error';
    }

    // Validación de contraseña
    $actualizar_clave = false;
    if (!empty($nueva_clave)) {
        if (strlen($nueva_clave) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            $error = true;
            $tipo_mensaje = 'error';
        } else {
            $hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
            $actualizar_clave = true;
        }
    }

    // Actualizar en la base de datos si no hay errores
    if (!$error) {
        if ($actualizar_clave) {
            $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, rol = ?, contraseña_hash = ? WHERE id = ?");
            $ejecutado = $stmt->execute([$usuario, $rol, $hash, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, rol = ? WHERE id = ?");
            $ejecutado = $stmt->execute([$usuario, $rol, $id]);
        }

        if ($ejecutado) {
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
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/editar_usuario.css">
</head>
<body>
    <h2>Editar Usuario</h2>

    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nombre de usuario:</label>
        <input type="text" name="usuario" value="<?= htmlspecialchars($usuario_actual['usuario']) ?>" required>

        <label>Rol:</label>
        <select name="rol" required>
            <option value="admin" <?= $usuario_actual['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="medico" <?= $usuario_actual['rol'] == 'medico' ? 'selected' : '' ?>>Medico</option>
            <option value="recepcionista" <?= $usuario_actual['rol'] == 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
        </select>

        <label>Nueva contraseña (opcional):</label>
        <input type="password" name="clave" placeholder="Dejar en blanco si no deseas cambiarla">

        <button type="submit">Actualizar</button>
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