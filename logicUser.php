<?php
session_start();


/**
 * USUARIO
 */

//Variable para controlar la dirección de subida de imágenes
const TARGET_DIR = "uploads/";

// Cargar la información del usuario desde la cookie, si existe
if (isset($_COOKIE['recuerdo']) && !empty($_COOKIE['recuerdo'])) {

    $usuariosJson = file_get_contents('../../data/users.json');
    $usuarios = json_decode($usuariosJson, true);

    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $_COOKIE['recuerdo']) {
            $_SESSION['user'] = $usuario; //Guardamos los datos del usuario en variable SESSION
             break;
        }
    }
    
}


// Verificamos la sesión o la cookie
if (!isset($_SESSION['user']) && !isset($_COOKIE['recuerdo'])) {
    header("Location: index.php");
    exit();
} else {
    $userData = $_SESSION['user']; //Guardamos los datos de la sesión de usuario mientras está conectado
}

if (empty(pathinfo(TARGET_DIR . $_SESSION['user']['imagen'], PATHINFO_EXTENSION))) {
    $_SESSION['user']['imagen'] = "default.jpg";
    $imagePath = TARGET_DIR .  $_SESSION['user']['imagen'];
}

// Manejo de cierre de sesión
if (isset($_POST['logout'])) {
    setcookie("recuerdo", "", time() - 3600, "/"); //Borramos la cookie
    session_destroy(); //desturimos la sesión
    header("Location: index.php"); //Redirigimos a index
    exit();
}

// Función para eliminar cuenta de usuario
function deleteUserAccount($userData)
{
    $usuariosJson = file_get_contents('../../data/users.json'); //Leemos el archivo JSON de usuarios
    $usuarios = json_decode($usuariosJson, true);

    //Búsqueda y verificación de datos de usuario para borrar
    foreach ($usuarios as $key => $usuario) {
        if ($usuario['nombre'] === $userData['nombre'] && $usuario['email'] === $userData['email']) {
            $imagePath = TARGET_DIR . $usuario['imagen']; //concatenamos a nivel local la dirección de imagen con su nombre
            if (file_exists($imagePath)) {
                unlink($imagePath); //Borramos la imagen después de comprobar que existe para evitar errores
            }
            unset($usuarios[$key]);//Borramos usuario
            break;
        }
    }

    if (file_put_contents('../../data/users.json', json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        return true; //guardamos de nuevo la información de usuarios  y comprobamos si todo el proceso se ha realizado correctamente
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
