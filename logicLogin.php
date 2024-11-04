<?php
session_start();
require "connection.php";

/** 
 * lOGIN
 */

//Funcion que sanitiza datos de entrada del usuario 
function test_input($data){
    return htmlspecialchars(stripslashes(trim($data)));
}

// Comprobamos si ya hay una sesión activa
if (isset($_SESSION['user'])) {
    header("Location: user.php");//Redirigimos a página de usuario
    exit();
}

//Recogemos los datos de usuario y nos preparamos para manejarlos
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['error'] = "Los campos no pueden estar vacíos.";
        header("Location: index.php");
        exit();
    } else {

        $username = test_input($_POST['username']);
        $userpassword = test_input($_POST['password']);

        try {
            $conn = connectDB();
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :emailDB");

            $stmt->bindParam(':emailDB', $username);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ((count($result) > 0) && (password_verify($userpassword, $result[0]['Passwd']))) {


                //Comprobamos si el usuario ha pulsado Recuérdame
                if (isset($_POST["recuerdo"])) {
                    setcookie("recuerdo", $result[0]['Token'], time() + 86400, "/", "", true, true);
                } else {
                    // Borramos la cookie si no se marca
                    setcookie("recuerdo", "", time() - 3600, "/");
                }

                $_SESSION['user'] = $result[0]; //guardamos la sesion de usuario
                
                header("Location: user.php"); // Redirige a la página de usuario
                exit();
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $conn = null;



        // Si las credenciales son incorrectas, recarga la pagina
        $_SESSION['error'] = "No se ha encontrado un usuario con esas credenciales.";
        header("Location: index.php");
        exit();
    }
}