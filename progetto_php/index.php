<?php
    include_once 'settings.php';
    include_once 'database.php';

    session_start();
    // se skip_login = false oppure non è settato
    if (!isset($skip_login) || !$skip_login) {
        // controlla quindi se c'è un user settato, altrimenti fa il login
        if (!isset($_SESSION['user'])) {
            if (!isset($_COOKIE['user'])) {
                header('Location: login.php');
                exit();
            }
            $_SESSION['user'] = $_COOKIE['user'];
        }
    } else { // Per sviluppo, saltiamo il login
        $_SESSION['user'] = 'pippo';
    }
?>

<!DOCTYPE html>
    <html lang="it">
    <head>
        <title>Progetto</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <?php
            echo '<h1>Benvenuto, ' . $_COOKIE['user'] . '</h1>';

            $user = get_user($_COOKIE['user']);
            $_SESSION['user'] = $user;
            $ts = $user['tesserasanitaria'];
            $cf = $user['cf'];
            $_SESSION['cf'] = $cf;
            $_SESSION['tesserasanitaria'] = $ts;
            echo '<p> Tipo utente: <b>';

            $tipoUtente = $_COOKIE['tipoUtente'];
            
            echo "$tipoUtente</b></p>";

            $_SESSION['tipoUtente'] = $tipoUtente;
            // pagina diversa in base al tipo di utente
            if($tipoUtente == 'Paziente'){
                echo '<ul> <li> <a href="prenota.php">Prenota esame</a> </li> <li> <a href="prenotazioni.php">Visualizza prenotazioni</a></li> <li> <a href="eliminaprenotazione.php">Elimina prenotazioni </a></li> <li> <a href="ricoveri.php">Visualizza Ricoveri </a></li></ul>';
                $_SESSION['tabella'] = 'prenotazione';
            } else if($tipoUtente == 'Medico'){
                echo '<ul><li> <a href="creaprescrizione.php">Prescrivi Esame </a></li><li> <a href="visualizzaprescrizione.php">Visualizza prescrizioni</li> </ul>';
            } else if ($tipoUtente == 'Personale amministrativo') {
                echo '<p> <a href="persAmm.php">Visualizza dati personale amministrativo</a> </p>';
            } else {
                echo '<p> <a href="dba.php">Visualizza dati DBA</a> </p>';
            }
        ?>
        <a href="logout.php">Logout</a>
    </body>
</html>
