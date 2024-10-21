<?php session_start() ?>

<!DOCTYPE HTML>
<html lang="es">

<head>
  <title>Registro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" charset="UTF-8" />
  <style>
    body {
      display: flex;
      justify-content: center;
      font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
    }

    span {
      color: red;
    }

    input[type="submit"] {
      padding: 10px 20px;
      font-size: 1em;
      border: none;
      border-radius: 5px;
      background-color: #356c8b;
      color: white;
      cursor: pointer;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div>
    <h2 style="text-align:center;">Registro</h2>
    <div>
      <p><span class="error">* campos requeridos</span></p>

      <form method="post" action="<?php echo htmlspecialchars("logicForm.php"); ?>" enctype="multipart/form-data">

        Nombre: <input type="text" name="name" size="31"
          value="<?php echo isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : ''; ?>">
        <span class="nameError">*
          <?php echo isset($_SESSION['nameError']) ? htmlspecialchars($_SESSION['nameError']) : '' ?>
        </span>
        <br><br>

        Apellidos: <input type="text" name="surname" size="29"
          value="<?php echo isset($_SESSION['form_data']['surname']) ? htmlspecialchars($_SESSION['form_data']['surname']) : ''; ?>">
        <span class="surnameError">*
          <?php echo isset($_SESSION['surNameError']) ? htmlspecialchars($_SESSION['surNameError']) : '' ?>
        </span>
        <br><br>

        E-mail: <input type="email" name="email" size="32"
          value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
        <span class="emailError">*
          <?php echo isset($_SESSION['emailError']) ? htmlspecialchars($_SESSION['emailError']) : '' ?>
        </span>
        <br><br>

        Fecha de nacimiento: <input type="date" name="age" style="width:165px"
          value="<?php echo isset($_SESSION['form_data']['age']) ? htmlspecialchars($_SESSION['form_data']['age']) : ''; ?>">
        <span class="ageError">*
          <?php echo isset($_SESSION['ageError']) ? htmlspecialchars($_SESSION['ageError']) : ''; ?>
        </span>
        <br><br>

        Contraseña: <input type="password" name="password" size="28">
        <span class="passwError">*
          <?php echo isset($_SESSION['passwordError']) ? htmlspecialchars($_SESSION['passwordError']) : '' ?>
        </span>
        <br><br>

        Repetir contraseña: <input type="PASSWORD" name="password2" size="21">
        <br><br>

        Imagen: <input type="file" name="fileToUpload" id="fileToUpdload">
        <span class="fileError">
          <?php echo isset($_SESSION['fileError']) ? htmlspecialchars($_SESSION['fileError']) : '' ?>
        </span>
        <br><br>

        <input type="submit" name="Enviar" value="Guardar y continuar">
      </form>
    </div>
  </div>
</body>

</html>