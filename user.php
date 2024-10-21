<?php
session_start();

const target_dir="uploads/";

// Si hay una cookie, cargamos la información del usuario (opcional)
if (isset($_COOKIE['recuerdo']) && !empty($_COOKIE['recuerdo'])) {

    $usuariosJson = file_get_contents('../data/users.json');
    $usuarios = json_decode($usuariosJson, true);

    foreach ($usuarios as $key => $usuario) {
        if ($usuario['email'] === $_COOKIE['recuerdo']) {
            $_SESSION['user'] = $usuario;
            $imagePath = target_dir . $usuario['imagen'];
            break;
        }
    }
}

// Verificamos si hay una sesión activa o si la cookie existe
if (!isset($_SESSION['user']) && !isset($_COOKIE['recuerdo'])) {
    header("Location: index.php"); // Redirigir al login si no está autentificado
    exit();
} else{
    $userData = $_SESSION['user'];
    $imagePath = target_dir . $userData['imagen'];
}


//Controlamos si el usuario cierra sesión
if (isset($_POST['logout'])) {
    setcookie("recuerdo", "", time() - 3600, "/");
    session_destroy();
    header("Location: index.php");
    exit();
}

// Función para eliminar cuenta de usuario
function deleteUserAccount($userData)
{
    // Leer el archivo JSON
    $usuariosJson = file_get_contents('../data/users.json');
    $usuarios = json_decode($usuariosJson, true);

    // Eliminar el usuario del array
    foreach ($usuarios as $key => $usuario) {
        if ($usuario['nombre'] === $userData['nombre'] && $usuario['email'] === $userData['email']) {
            $imagePath = target_dir. $usuario['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            unset($usuarios[$key]);
            setcookie("username", "", time() - 3600, "/");
            break;
        }
    }

    // Guardar de nuevo el archivo JSON
    if (file_put_contents('../data/users.json', json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        return true;
    } else {
        return false;
    }
}

if (isset($_POST['delete_account'])) {
    if (deleteUserAccount($userData)) {
        session_destroy();
        header("Location: index.php"); // Redirigimos al login
        exit();
    } else {
        $_SESSION['error'] = "No se pudo borrar la cuenta. Inténtalo de nuevo más tarde.";
        header("Location: user_page.php"); // Regresar a la página de usuario
        exit();
    }
}

/*$usuariosJson = @file_get_contents('../data/users.json');
if ($usuariosJson === false) {
    // Handle the error gracefully
    $_SESSION['error'] = "No se pudo cargar la información de los usuarios.";
    header("Location: error_page.php");
    exit();
}
$usuarios = json_decode($usuariosJson, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['error'] = "Error al procesar los datos de usuarios.";
    header("Location: error_page.php");
    exit();
}*/

?>

<!DOCTYPE HTML>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Usuario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        h3 {
            margin-bottom: 10px;
        }

        img {
            object-fit: contain;
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }

        form {
            margin-top: 20px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 5px;
        }

        button:hover {
            background-color: #2980b9;
        }

        button:active {
            background-color: #1a6f94;
        }
    </style>
</head>

<body>
    <h1>Bienvenido, <?php echo htmlspecialchars($userData['nombre'] . " " . $userData['apellidos']); ?>!</h1>
    <h3>Tus datos son:</h3>
    <p>Email: <?php echo htmlspecialchars($userData['email']); ?></p>
    <p>Edad: <?php echo htmlspecialchars($userData['edad']); ?></p>
    <p><img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Imagen de perfil"></p>
    <form method="post">
        <button type="submit" name="logout">Cerrar sesión</button>
        <button type="submit" name="delete_account"
            onclick="return confirm('¿Estás seguro de que deseas borrar tu cuenta? Esta acción no se puede deshacer.');">Borrar
            cuenta</button>
    </form>
</body>

</html>