<?php
session_start();
include "connection.php";

const TARGET_IMG = "uploads/";   //Variable de dirección de subida de imágenes de usuario
$error = false;                  //Variable de control de errores
$_SESSION['form_data'] = $_POST; //Variable para devolver datos correctos al usuario cuando se equivoca en uno o más campos

/**
 * FORMULARIO
 */

//Variables para controlar los datos de usuario
$name = $surname = $email = $birth = $age = $password1 = $password2 = $fileToUpload = $Passwd = $Token = "";

//Funcion que recoge los datos y los pasa por una función antes de guardarlos en sus variables correspondientes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $name = test_input($_POST["name"]);
   $surname = test_input($_POST["surname"]);
   $email = test_input($_POST["email"]);
   $birth = test_input($_POST["age"]);
   $fileToUpload = $_FILES["fileToUpload"];
   $password1 = test_input($_POST["password"]);
   $password2 = test_input($_POST["password2"]);
}


//Funcion que sanitiza datos de entrada del usuario 
function test_input($data){
   return htmlspecialchars(stripslashes(trim($data)));
}

//funcion para comprobar datos de tipo texto (Nombre y Apellido/s)
function test_text($data)
{
   $textpattern = "/^[a-zA-ZÀ-ÿ]+(\s*[a-zA-ZÀ-ÿ]*)*[a-zA-ZÀ-ÿ]+$/";
   return preg_match_all($textpattern, $data);
}

//Funcion que transforma la entrada de datos a formato Aaaa y acepta tildes
function capitalFirst($string)
{
   $vocals = ['Á' => 'á', 'É' => 'é', 'Í' => 'í', 'Ó' => 'o', 'Ú' => 'u'];
   $string = explode(" ", $string);
   $result = [];
   foreach ($string as $str) {
      $str = mb_strtolower(strtr($str, $vocals));
      $first = mb_substr($str, 0, 1);
      $first = strtr($first, array_flip($vocals));
      $first = mb_strtoupper($first);
      $result[] = $first . mb_substr($str, 1);
   }
   return implode(" ", $result);
}

//Comprobamos el nombre
if (!empty($name)) {
   if (test_text($name) === 0) {
      $_SESSION["nameError"] = "El nombre sólo puede contener letras";
      $error = true;
   } else {
      $name = capitalFirst($name);
      unset($_SESSION["nameError"]);
   }
} else {
   $_SESSION["nameError"] = "El nombre es obligatorio";
   $error = true;
}


//Comprobamos los Apellidos
if (!empty($surname)) {
   if (test_text($surname) === 0) {
      $_SESSION["surNameError"] = "El apellido solo puede contener letras";
      $error = true;
   } else {
      $surname = capitalFirst($surname);
      unset($_SESSION["surNameError"]);
   }
} else {
   $_SESSION["surNameError"] = "El apellido es obligatorio";
   $error = true;
}


//Validación del email con comprobación en servidor de que no se repita
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
   
   try {
      $conn = connectDB();
      $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = :emailBD");

      //Parametrizamos la variable de entrada para evitar inyección
      $stmt->bindParam(':emailBD', $email);
      $stmt->execute();

      // Recogemos todos los datos en un array
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($result) > 0) {
         $_SESSION["emailError"] = "E-mail ya registrado";
         $error = true;
      } else {
         $Token = uniqid('Token_',true);
         unset($_SESSION["emailError"]);
      }
   } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
   }
   $conn = null;
} else {
   $_SESSION["emailError"] = "El e-mail es obligatorio";
   $error = true;
}

//Devolvemos a user si la cookie está creada para recordar y se intenta acceder al formulario por destino
if (isset($_COOKIE['username']) && !empty($_COOKIE['username'])) {
   header("Location: user.php");
   exit();
}

//Comprobamos la fecha de nacimiento y si es correcta, la edad
if (empty($birth) || !DateTime::createFromFormat('Y-m-d', $birth)) {
   $_SESSION["ageError"] = "La fecha no puede estar vacía o no es válida";
   $error = true;
} else {
   $today = new DateTime();
   $datePassed = new DateTime($birth);
   $diff = $today->diff($datePassed);
   $age = $diff->y;
   if ($diff->y < 18) {
      $_SESSION["ageError"] = "Debes ser mayor de 18 años";
      $error = true;
   } else {
      unset($_SESSION["ageError"]);
   }
}


//Comprobamos las contraseñas

if (!empty($password1)) {
   if ($password1 === $password2) {
      $pattern = "/^(?=.*[A-ZÑÁÉÍÓÚ])(?=.*[a-zñáéíóú])(?=.*\d)(?=.*[!@#$%^&*()-=_+{};:,<.>]).{8,20}$/";

      if (preg_match_all($pattern, $password1)) {
         unset($_SESSION["passwordError"]);
         $Passwd = password_hash($password1, PASSWORD_DEFAULT);
      } else {
         $_SESSION["passwordError"] = "La contraseña no cumple las reglas de seguridad";
         $error = true;
      }
   } else {
      $_SESSION["passwordError"] = "Las contraseñas no coinciden";
      $error = true;
   }
} else {
   $_SESSION["passwordError"] = "La contraseña es obligatoria";
   $error = true;
}


/**
 * IMAGEN FORMULARIO
 */

// Validaciones del archivo
$target_file = TARGET_IMG . basename($fileToUpload["name"]);//
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$randomNameFile = uniqid('file_', true) . '.' . $imageFileType;
$upload = TARGET_IMG . $randomNameFile;

if ($fileToUpload["error"] == 0 && !$error) {
   if (getimagesize($fileToUpload["tmp_name"]) === false) {
      $_SESSION["fileError"] = "El archivo no es una imagen.";
      $uploadOk = 0;
      $error = true;
   } elseif (file_exists($target_file)) {
      $_SESSION["fileError"] = "La imagen ya existe.";
      $uploadOk = 0;
      $error = true;
   } elseif ($fileToUpload["size"] > 500000) {
      $_SESSION["fileError"] = "La imagen es demasiado grande.";
      $uploadOk = 0;
      $error = true;
   } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
      $_SESSION["fileError"] = "Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
      $uploadOk = 0;
      $error = true;
   } elseif ($uploadOk === 1 && !move_uploaded_file($fileToUpload["tmp_name"], $upload)) {
      $_SESSION["fileError"] = "Error al subir la imagen.";
      $error = true;
   } else {
      unset($_SESSION["fileError"]);
   }
} else {
   unset($_SESSION["fileError"]);
}
//Comprobados todos los datos, guardamos en BD o recargamos formulario
if ($error) {

   header("Location: formulary.php");
   exit();

} else {
   
   try {
      $conn = connectDB();

      // Preparamos la inserción de usuario
      $stmt = $conn->prepare("INSERT INTO Usuarios (Firstname, Lastname, email, Birth, Passwd, Imagepath, Token)
      VALUES (:Firstname, :Lastname, :email, :Birth, :Passwd, :Imagepath, :Token)");
      $stmt->bindParam(':Firstname', $firstnameDB);
      $stmt->bindParam(':Lastname', $lastnameDB);
      $stmt->bindParam(':email', $emailDB);
      $stmt->bindParam(':Birth', $BirthDB);
      $stmt->bindParam(':Passwd', $PasswdDB);
      $stmt->bindParam(':Imagepath', $ImagepathDB);
      $stmt->bindParam(':Token', $TokenDB);

      // asigamos los paramétricos
      $firstnameDB = $name;
      $lastnameDB = $surname;
      $emailDB = $email;
      $BirthDB = $birth;
      $PasswdDB = $Passwd;
      $ImagepathDB = $randomNameFile;
      $TokenDB = $Token;
      $stmt->execute();

   } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
   }
   $conn = null;


   //Terminar sesion y salir a login
   session_destroy();
   header("Location: index.php");
   exit(); 
}