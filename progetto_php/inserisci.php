<?php
    
    include_once 'database.php';
    include_once 'settings.php';
    session_start();
    // se non c'è un utente loggato e non c'è un cookie di login
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
    // se la form viene inviata
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // mi porto la tabella e la sua intestazione
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        $values = [];
        // mi salvo i valori
        foreach ($schema as $column) {
            $values[$column] = $_POST[$column];
        }
        // inserisco la tupla
        $arr = inserisci($table, $values);
        if (!$arr[0]) {
            echo 'Errore: ' . $arr[1];
            exit();
        }
        echo 'Inserimento avvenuto con successo';
        header('Refresh: 2; url = dba.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        //mi porto la tabella e la sua intestazione
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        // stampo la form per l'inserimento
        echo '<form action="inserisci.php" method="post">';
        echo '<h2> Inserimento in ' . $table . '</h2>';
        echo '<table>';
        foreach ($schema as $column) {
            echo '<tr>';
            echo '<td><label for="' . $column . '">' . $column . ' </label></td>';
            echo '<td><input type="text" id="' . $column . '" name="' . $column . '"></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<br>';
        echo '<input type="submit" value="Invia">';
        echo '</form>';
        echo '<br>';
        echo '<a href="dba.php">Torna alla home</a>';
    ?>
</body>
</html>