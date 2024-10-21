<?php
session_start();

/** 
 * Funcion que sanitiza datos de entrada del usuario 
 */
function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
 }

// Comprobamos si ya hay una sesión activa -- Revisar
if (isset($_SESSION['user'])) {
    header("Location: user.php");
    exit();
}

//Recogemos los datos de usuario y nos preparamos para manejarlos
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['error'] = "Los campos no pueden estar vacíos.";

        header("Location: index.php");
        exit();
    }

    $username = test_input($_POST['username']);
    $userpassword = test_input($_POST['password']);

    //Comparamos los datos recogidos y saneados con los guardados en JSON
    $users = json_decode(file_get_contents('../data/users.json'), true);

    foreach ($users as $user) {
        if (($user['nombre'] === $username || $user['email'] === $username) && password_verify($userpassword, $user['contraseña'])) {
            $_SESSION['user'] = $user; // Guarda los datos del usuario en la sesión
            
            //Comprobamos si el usuario ha pulsado Recuérdame
            if (isset($_POST["recuerdo"])) {
                setcookie("recuerdo", $user['email'], time() + 86400, "/");
            } else {
                // Borramos la cookie si no se marca
                if (isset($_COOKIE["recuerdo"])) {
                    setcookie("recuerdo", "", time() - 3600, "/");
                }
            }
            
            header("Location: user.php"); // Redirige a la página de usuario
            exit();
        }
    }

    // Si las credenciales son incorrectas, recarga la pagina
    $_SESSION['error'] = "No se ha encontrado un usuario con esas credenciales.";
    header("Location: index.php");
    exit();
}