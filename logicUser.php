<?php
session_start();

const TARGET_DIR = "uploads/";

// Cargar la información del usuario desde la cookie, si existe
if (isset($_COOKIE['recuerdo']) && !empty($_COOKIE['recuerdo'])) {
    $usuariosJson = file_get_contents('../data/users.json');
    $usuarios = json_decode($usuariosJson, true);

    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $_COOKIE['recuerdo']) {
            $_SESSION['user'] = $usuario;
            $imagePath = TARGET_DIR . $usuario['imagen'];
            break;
        }
    }
}

// Verificamos la sesión o la cookie
if (!isset($_SESSION['user']) && !isset($_COOKIE['recuerdo'])) {
    header("Location: index.php");
    exit();
} else {
    $userData = $_SESSION['user'];
    $imagePath = TARGET_DIR . $userData['imagen'];
}

// Manejo de cierre de sesión
if (isset($_POST['logout'])) {
    setcookie("recuerdo", "", time() - 3600, "/");
    session_destroy();
    header("Location: index.php");
    exit();
}

// Función para eliminar cuenta de usuario
function deleteUserAccount($userData)
{
    $usuariosJson = file_get_contents('../data/users.json');
    $usuarios = json_decode($usuariosJson, true);

    foreach ($usuarios as $key => $usuario) {
        if ($usuario['nombre'] === $userData['nombre'] && $usuario['email'] === $userData['email']) {
            $imagePath = TARGET_DIR . $usuario['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            unset($usuarios[$key]);
            break;
        }
    }

    if (file_put_contents('../data/users.json', json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        return true;
    }
    return false;
}

// Manejo de la eliminación de la cuenta
if (isset($_POST['delete_account'])) {
    if (deleteUserAccount($userData)) {
        session_destroy();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "No se pudo borrar la cuenta. Inténtalo de nuevo más tarde.";
        header("Location: user_page.php");
        exit();
    }
}
?>