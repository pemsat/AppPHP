
<?php include 'logicUser.php'; ?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Usuario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        h3 {
            margin-bottom: 10px;
        }

        img {
            object-fit: contain;
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }

        form {
            margin-top: 20px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 5px;
        }

        button:hover {
            background-color: #2980b9;
        }

        button:active {
            background-color: #1a6f94;
        }
    </style>
</head>
<body>
    <h1>Saludos, <?php echo htmlspecialchars($userData['FirstName'] . " " . $userData['LastName']); ?>!</h1>
    <h3>Tus datos son:</h3>
    <p>Email: <?php echo htmlspecialchars($userData['email']); ?></p>
    <p>Edad: <?php echo htmlspecialchars($userData['Birth']); ?></p>
    <p><img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Imagen de perfil"></p>
    <form method="post">
        <button type="submit" name="logout" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">Cerrar sesión</button>
        <button type="submit" name="delete_account" onclick="return confirm('¿Estás seguro de que deseas borrar tu cuenta? Esta acción no se puede deshacer.');">Borrar cuenta</button>
    </form>
</body>
</html>
