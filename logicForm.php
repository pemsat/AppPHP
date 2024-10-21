<?php
//Inicio de sesión
session_start();
$error = false;
$_SESSION['form_data'] = $_POST;

/**
 * Variables para recoger datos y sanitizados
 */
$name = $surname = $email = $birth = $age = $password1 = $password2 = $fileToUpload = "";

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

/** 
 * Funcion que sanitiza datos de entrada del usuario 
 */
function test_input($data)
{
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

/**
 * funcion para comprobar datos de tipo texto (Nombre y Apellido/s)
 */
function test_text($data)
{
   $textpattern = "/^[a-zA-ZÀ-ÿ\u00f1\u00d1]+(\s*[a-zA-ZÀ-ÿ\u00f1\u00d1]*)*[a-zA-ZÀ-ÿ\u00f1\u00d1]+$/";
   return preg_match_all($textpattern, $data);
}
/**
 * Comprobamos el nombre
 */
if (!empty($name)) {
   if (test_text($name) === 0) {
      $_SESSION["nameError"] = "El nombre sólo puede contener letras";
      $error = true;
   } else
      unset($_SESSION["nameError"]);
} else {
   $_SESSION["nameError"] = "El nombre es obligatorio";
   $error = true;
}

/**
 * Comprobamos los Apellidos
 */
if (!empty($surname)) {
   if (test_text($surname) === 0) {
      $_SESSION["surNameError"] = "El apellido solo puede contener letras";
      $error = true;
   } else {
      unset($_SESSION["surNameError"]);
   }
} else {
   $_SESSION["surNameError"] = "El apellido es obligatorio";
   $error = true;
}

/**
 * Comprobación del email
 */
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
   $usuariosJson = file_get_contents('../data/users.json');
   $usuarios = json_decode($usuariosJson, true);

   foreach ($usuarios as $key => $usuario) {
      if ($usuario['email'] === $email) {
         $_SESSION["emailError"] = "E-mail ya registrado";
         $error = true;
      } else {
         unset($_SESSION["emailError"]);
      }
   }
} else {
   $_SESSION["emailError"] = "El e-mail es obligatorio";
   $error = true;
}


if (isset($_COOKIE['username']) && !empty($_COOKIE['username'])) {



}
/**
 * Comprobamos la fecha de nacimiento y si es correcta, la edad
 */
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

/**
 * Comprobamos las contraseñas
 */
if (!empty($password1)) {
   if ($password1 === $password2) {
      $pattern = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{10,}$/";

      if (preg_match_all($pattern, $password1)) {
         unset($_SESSION["passwordError"]);

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
 * Comprobamos los datos de la imagen
 */
// Validaciones del archivo
$target_dir = "uploads/";
$target_file = $target_dir . basename($fileToUpload["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$randomNameFile = uniqid('file_',true) . '.' . $imageFileType;
$upload = $target_dir . $randomNameFile;

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
//Comprobados todos los datos, guardamos en JSON o recargamos formulario
if ($error) {

   header("Location: formulary.php");
   exit();

} else {
   $userData = [
      'nombre' => $name,
      'apellidos' => $surname,
      'email' => $email,
      'edad' => $age,
      'contraseña' => password_hash($password1, PASSWORD_DEFAULT), // Guarda la contraseña de forma segura
      'imagen' => $randomNameFile
   ];

   // Leer el archivo JSON existente
   $filePath = '../data/users.json';

   $currentData = [];
   if (file_exists($filePath)) {
      $currentData = json_decode(file_get_contents($filePath), true);
   }

   // Agregar el nuevo usuario
   $currentData[] = $userData;

   // Guardar el nuevo conjunto de datos en el archivo JSON
   file_put_contents($filePath, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

   //Terminar sesion y salir a login
   session_destroy();
   header("Location: index.php");
   exit();
}