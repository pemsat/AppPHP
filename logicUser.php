<?php
session_start();
include 'connection.php';

/**
 * USUARIO
 */

//Variable para controlar la dirección de subida de imágenes
const TARGET_DIR = "uploads/";

// Cargar la información del usuario desde la cookie, si existe
if (isset($_COOKIE['recuerdo']) && !empty($_COOKIE['recuerdo'])) {

    try {
        $conn = connectBD();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :emailBD");
        // Bind the parameter to prevent SQL injection
        $stmt->bindParam(':emailBD', $_COOKIE['recuerdo']);
        $stmt->execute();

        // Fetch the resulting row(s) as an associative array
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $_SESSION["user"] = $result;

        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

}


// Verificamos la sesión o la cookie
if (!isset($_SESSION['user']) && !isset($_COOKIE['recuerdo'])) {
    header("Location: index.php");
    exit();
} else {
    $userData = $_SESSION['user'][0]; //Guardamos los datos de la sesión de usuario mientras está conectado
}

if (empty(pathinfo(TARGET_DIR . $userData['Imagepath'], PATHINFO_EXTENSION)) || !file_exists($userData['ImagePath'])) {
    $userData['Imagepath'] = "default.jpg";
    $imagePath = TARGET_DIR . $userData['Imagepath'];
} 

// Manejo de cierre de sesión
if (isset($_POST['logout'])) {
    setcookie("recuerdo", "", time() - 3600, "/"); //Borramos la cookie
    session_destroy(); //desturimos la sesión
    header("Location: index.php"); //Redirigimos a index
    exit();
}

// Función para eliminar cuenta de usuario
function deleteUserAccount($email)
{
    /* $usuariosJson = file_get_contents('../../data/users.json'); //Leemos el archivo JSON de usuarios
    $usuarios = json_decode($usuariosJson, true);

    //Búsqueda y verificación de datos de usuario para borrar
    foreach ($usuarios as $key => $usuario) {
        if ($usuario['email'] === $userData['email']) {
            unlink($imagePath); //Borramos la imagen
            unset($usuarios[$key]);//Borramos usuario
            break;
        }
    }

    if (file_put_contents('../../data/users.json', json_encode(array_values($usuarios), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        return true; //guardamos de nuevo la información de usuarios  y comprobamos si todo el proceso se ha realizado correctamente
    }
    return false;
 */
    try {
        $conn = connectBD();

        $stmt = $conn->prepare("DELETE FROM usuarios WHERE email = :email");
        // Bind the parameter to prevent SQL injection
        $stmt->bindParam(':emailBD', $email);


        if ($stmt->execute()) {
            return true;
        } else
            return false;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

// Manejo de la eliminación de la cuenta
if (isset($_POST['delete_account'])) {
    if (deleteUserAccount($userData['email'])) {
        session_destroy();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "No se pudo borrar la cuenta. Inténtalo de nuevo más tarde.";
        header("Location: user_page.php");
        exit();
    }
}