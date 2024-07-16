<?php
    include_once 'settings.php';
    include_once 'database.php';

    session_start();
    // se skip_login = false oppure non è settato
    if (!isset($skip_login) || !$skip_login) {
        // controlla quindi se c'è un user settato, altrimenti fa il login
        if (!isset($_SESSION['user']) && !isset($_COOKIE['user'])) {
            header('Location: login.php');
            exit();
        }
    } else { // Per sviluppo, saltiamo il login
        $_SESSION['user'] = 'pippo';
    }
    // se il tipo di utente non è Paziente reindirizzo alla pagina principale
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'Paziente') {
        header('Location: index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazioni</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        $user = get_user($_COOKIE['user']);
        $ricoveri = get_ricoveri($user['tesserasanitaria']);
        echo '<h2>Ricoveri</h2>';
        echo '<table>';
        echo '<tr>';
        echo '<th>codletto</th>';
        echo '<th>DataRicovero</th>';
        // echo '<th>nTesSanitaria</th>'; // da nascondere
        echo '<th>DataDimissione</th>';
        echo '<th>codOspedale</th>';
        echo '<th>Reparto</th>';
        echo '</tr>';
        foreach ($ricoveri as $ricovero) {
            echo '<tr>';
            echo '<td>' . $ricovero['codletto'] . '</td>';
            echo '<td>' . $ricovero['dataricovero'] . '</td>';
            //echo '<td>' . $ricovero['ntessanitariapaziente'] . '</td>';
            echo '<td>' . $ricovero['datadimissione'] . '</td>';
            echo '<td>' . $ricovero['codospedale'] . '</td>';
            echo '<td>' . $ricovero['nomereparto'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<br>';
        echo '<a href="index.php"> Torna alla home</a>'
    ?>
</body>
</html>