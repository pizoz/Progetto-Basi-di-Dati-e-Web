<?php

    include_once 'settings.php';
    session_start();

    // se arriva una richiesta di post (attraverso la form sotto) controlla che username e
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        include_once 'database.php';
        // controllo attraverso il database se le credenziali siano valide
        if (!check_login($username, $password)) {
            header('Refresh: 2; url=login.php');
            echo 'Invalid username or password';
            exit();
        }
        // se username e password matchano allora salvo il nome utente
        $_SESSION['user'] = $username;
        setcookie('user', $username, time() + 3600*24);
        $user = get_user($username);
        $ts = $user['tesserasanitaria'];
        $cf = $user['cf'];
        $tipoUtente = null;
        // selezione tipo utente in base alla tabella nel DB
        if($ts == null && $cf == null){
            $tipoUtente = 'DBA';
        } else if($cf != null && $ts == null){
            
            if (!isinPersonaleamministrativo($cf)) {
                $tipoUtente = 'Medico';
                $_SESSION['cf'] = $cf;
            } else {
                $tipoUtente = 'Personale amministrativo';
                $_SESSION['cf'] = $cf;
            }
        } else {
            $tipoUtente = 'Paziente';
            $_SESSION['tesserasanitaria'] = $ts;
        }
        setcookie('tipoUtente', $tipoUtente, time() + 3600*24);
        //redirect alla home
        header('Location: index.php');
        exit();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" value="Login">
    </form>
</body>
</html>
