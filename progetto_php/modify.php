<?php

    session_start();
    include_once 'settings.php';
    include_once 'database.php';
    // se non è loggato nessun utente o non è settato il cookie di login reindirizzo alla pagina di login
    if (!isset($_SESSION['user']) && !isset($_COOKIE['user'])) {
        header('Location: login.php');
        exit();
    }
    // se il tipo di utente non è DBA reindirizzo alla pagina principale
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'DBA') {
        header('Location: index.php');
        exit();
    }
    // se la form viene inviata: modifico i valori della tupla con lo stesso indice e ne modifico i valori
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        $values = [];
        foreach ($schema as $column) {
            $values[$column] = $_POST[$column];
        }
        $res = modify($values);
        if (!$res[0]) {
            echo 'Errore: ' . $res[1];
            exit();
        }
        echo 'Modifica avvenuta con successo';
        header('Refresh: 2; url = dba.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica tupla</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        $table = $_SESSION['tabella'];
        $chiavi = get_primary_keys($table);
        $schema = get_schema($table);
        $values = $_SESSION['values'];
        $valori_chiavi = [];
        echo '<h2> Modifica in ' . $table . '</h2>';
        echo '<form action="modify.php" method="post">';
        echo '<table>';
        echo '<tr>';
        foreach ($schema as $column) {
            echo '<th>' . $column . '</th>';
        }
        echo '</tr>';
        echo '<tr>';
        foreach ($schema as $column) {
            // controllo tra glia ttributi della tabella stampata, quali sono chiave
            // -> se sono chiave allora stampo il valore
            // -> se non sono chiave allora stampo un input per la modifica
            if (in_array($column, $chiavi)) {
                echo '<td><input type="text" name="' . $column . '" value="' . $values[$column] . '" readonly></td>';
                $valori_chiavi[$column] = $values[$column];
            } else {
                echo '<td><input type="text" name="' . $column . '" value="' . $values[$column] . '"></td>';
            }
        }
        echo '</tr>';
        echo '</table>';
        echo '<br>';
        echo '<input type="submit" value="Invia">';
        echo '</form>';
        echo '<br>';
        echo '<a href="modifica.php">Torna alla pagina di scelta</a>';
        echo '<br>';
        echo '<a href="dba.php">Torna alla home</a>'
    ?>
</body>
</html>