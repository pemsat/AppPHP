<!DOCTYPE html>
<html>

<?php session_start() ?>
<?php
if (isset($_COOKIE['recuerdo'])) {
    header("Location: user.php");
}
?>

<head>
    <title>Inicio</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="screen">
            <div class="screen__content">
                <form class="login" method="post" action="<?php echo htmlspecialchars("logicLogin.php"); ?>">
                    <div class="login__field">
                        <i class="login__icon fas fa-user"></i>
                        <input type="text" name="username" class="login__input" placeholder="Nombre / Email">
                    </div>
                    <div class="login__field">
                        <i class="login__icon fas fa-lock"></i>
                        <input type="password" name="password" class="login__input" placeholder="Contraseña">
                    </div>
                    <div class="error-message">
                        <?php echo isset($_SESSION['error']) ? htmlspecialchars($_SESSION['error']) : '' ?>
                    </div>
                    <div>
                        <label for="recuerdo" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>Recordarme</label>
                        <input type="checkbox" name="recuerdo" />
                    </div>
                    <button class="button login__submit">
                        <span class="button__text">Acceder</span>
                        <i class="button__icon fas fa-chevron-right"></i>
                    </button>
                </form>
                <div class="additional-options">
                    <a name="registro" href="formulary.php">Registrarse</a>
                </div>
            </div>
            <div class="screen__background">
                <span class="screen__background__shape screen__background__shape4"></span>
                <span class="screen__background__shape screen__background__shape3"></span>
                <span class="screen__background__shape screen__background__shape2"></span>
                <span class="screen__background__shape screen__background__shape1"></span>
            </div>
        </div>
    </div>
</body>

</html>