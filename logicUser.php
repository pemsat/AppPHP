<?php
session_start();
require "connection.php";


/**
 * USUARIO
 */

//Variable para controlar la dirección de subida de imágenes
const TARGET_IMG = "uploads/";

// Cargar la información del usuario desde la cookie, si existe
if (isset($_COOKIE['recuerdo']) && !empty($_COOKIE['recuerdo'])) {

    try {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE Token = :TokenDB");
        // Bind the parameter to prevent SQL injection
        $stmt->bindParam(':TokenDB', $_COOKIE['recuerdo']);
        $stmt->execute();

        // Fetch the resulting row(s) as an associative array
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 0) {
            $_SESSION["user"] = $result[0];

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
    $userData = $_SESSION['user']; //Guardamos los datos de la sesión de usuario mientras está conectado
}

if (empty(pathinfo($userData['Imagepath'], PATHINFO_EXTENSION)) || !file_exists(TARGET_IMG.$userData['Imagepath'])) {
    $userData['Imagepath'] = "default.jpg";
}
$imagePath = TARGET_IMG . $userData['Imagepath'];

// Manejo de cierre de sesión
if (isset($_POST['logout'])) {
    setcookie("recuerdo", "", time() - 3600, "/"); //Borramos la cookie
    session_destroy(); //desturimos la sesión
    header("Location: index.php"); //Redirigimos a index
    exit();
}

// Función para eliminar cuenta de usuario
function deleteUserAccount($email,$image)
{
    try {
        $conn = connectDB();

        $stmt = $conn->prepare("DELETE FROM usuarios WHERE email = :emailBD");
        // Bind the parameter to prevent SQL injection
        $stmt->bindParam(':emailBD', $email);


        if ($stmt->execute()) {
            unlink($image);
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
    if (deleteUserAccount($userData['email'],TARGET_IMG.$_SESSION['user']['Imagepath'])) {
        setcookie("recuerdo", "", time() - 3600, "/");
        session_destroy();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "No se pudo borrar la cuenta. Inténtalo de nuevo más tarde.";
        header("Location: user.php");
        exit();
    }
}