<?php
include '../includes/auth.php';
if (!es_admin()) die("Acceso restringido.");

include '../includes/conexion.php';

$mensaje = "";
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensajes_permitidos = ['eliminado', 'registrado', 'actualizado'];

    if (in_array($_GET['mensaje'], $mensajes_permitidos)) {
        switch ($_GET['mensaje']) {
            case 'eliminado':
                $mensaje = "Usuario eliminado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'registrado':
                $mensaje = "Usuario creado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'actualizado':
                $mensaje = "Usuario actualizado con éxito.";
                $tipo_mensaje = "exito";
                break;
            case 'error':
                $mensaje = "Ocurrió un error. Intenta nuevamente.";
                $tipo_mensaje = "error";
                break;
            case 'errorexiste':
                $mensaje = "Lo sentimos, este usuario no existe";
                $tipo_mensaje = "error";
                break;
            case 'erroreliminar':
                $mensaje = "No puedes eliminar tu propio usuario.";
                $tipo_mensaje = "error";
                break;
        }
    }
}

$sql = "SELECT id, usuario, rol FROM usuarios ORDER BY usuario ASC";
$usuarios = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="../css/listar_usuarios.css">
</head>
<body>

    <div class="top-bar">
        <h2>Usuarios del sistema</h2>
        <div class="buttons">
            <a href="../dashboard.php"><button class="btn-volver">← Volver</button></a>
            <a href="agregar.php"><button class="btn-primary">Nuevo Usuario</button></a>
        </div>
    </div>
 
    <?php if (!empty($mensaje)): ?>
        <p class="mensaje <?= $tipo_mensaje ?>"><?= $mensaje ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                    <td><?= $u['rol'] ?></td>
                    <td class="actions">
                        <?php if ($u['usuario'] !== $_SESSION['usuario']): ?>
                            <a href="editar.php?id=<?= $u['id'] ?>">Editar</a>
                            <a href="confirmar_eliminacion.php?id=<?= $u['id'] ?>">Eliminar</a>
                        <?php else: ?>
                            <span style="color: gray;">(Tú)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        setTimeout(() => {
            const mensaje = document.querySelector('.mensaje');
            if (mensaje) mensaje.style.display = 'none';
        }, 3000);
    </script>

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
