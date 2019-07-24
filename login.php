<?php require_once "controller/prozess.php"?>
<!DOCTYPE html>
<html lang="de">
    <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
    </head>
        <body>
            <div class="container" >
                <div class="row">
                    <div class="col-md-4 offset-md-3 form-div login" >
                    <form action="login.php" method="post">
                    <h3 class="text-center">Login</h3>     
                    <!-- Zeigen auf fehlenden Eingabe-Felder -->
                    <?php if(count($errors)> 0): ?>
                        <div class ="alert alert-danger">
                            <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username">Benutzer</label>
                        <input type="text" name="vornameUsers" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password">Passwort</label>
                        <input type="password" name="nameUsers" class="form-control">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="login-btn" class="btn btn-primary btn-block btn-lg">Login</button>
                    </div>
                        <p class="text-center">Sie sind noch kein Mitglied?<a href="signup.php"> Registrieren</a></p>
                    </form>
                </div>
            </body>
</html>
