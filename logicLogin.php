<?php
session_start();
include 'connection.php';

/** 
 * lOGIN
 */

//Funcion que sanitiza datos de entrada del usuario 
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
            $conn = connectBD();
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :emailBD");
            // Bind the parameter to prevent SQL injection
            $stmt->bindParam(':emailBD', $username);
            $stmt->execute();

            // Fetch the resulting row(s) as an associative array
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ((count($result) > 0) && (password_verify($userpassword, $result[0]['Passwd']))) {
                $_SESSION["user"] = $result;
                //Comprobamos si el usuario ha pulsado Recuérdame
                if (isset($_POST["recuerdo"])) {
                    setcookie("recuerdo", $result[0]['email'], time() + 86400, "/");
                } else {
                    // Borramos la cookie si no se marca
                    if (isset($_COOKIE["recuerdo"])) {
                        setcookie("recuerdo", "", time() - 3600, "/");
                    }
                }

                $conn = null;
                header("Location: user.php");
                exit();

            } else {
                $_SESSION["error"] = "No se ha encontrado un usuario con esas credenciales.";
                //header("Location: index.php");
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $conn = null;
    }
}