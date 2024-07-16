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
        // se la tupla da modificare non è stata selezionata reindirizzo alla pagina di modifica
        if (!isset($_POST['selectedRow'])) {
            header('Location: modifica.php');
            exit();
        }
        // seleziono la tupla con lo stesso indice e me la porto in modify.php
        $selectedRow = $_POST['selectedRow'];
        $query = "SELECT * FROM $table";
        $result = pg_query($query);
        $counter = 0;
        $values = [];
        while ($row = pg_fetch_assoc($result)) {
            if ($counter == $selectedRow) {
                foreach ($schema as $column) {
                    $values[$column] = $row[$column];
                }
                $_SESSION['values'] = $values;
                header('Location: modify.php');
                exit();
            }
            $counter++;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php

        //stampo la tabella

        $table = $_SESSION['tabella'];

        echo "<h2>Modifica in $table</h2>";    
        $schema = get_schema($table);
    
        $query = "SELECT * FROM $table";
        $result = pg_query($query);

        echo "<table>";
    
        echo "<tr>";
        // Add the column header for selection
        foreach ($schema as $columnName) {
            echo "<th>$columnName</th>";
        }
        echo "<th>Select</th>";
        echo "</tr>";
        echo "<form action='modifica.php' method='post'>";
        // stampa the table rows
        $counter = 0;
        while ($row = pg_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($schema as $columnName) {
                echo "<td>" . $row[$columnName] . "</td>";
            }
            echo "<td><input type='radio' name='selectedRow' value='{$counter}'></td>"; // Add the radio button for selection
            $counter++; 
            echo "</tr>";
        }
        echo "</table>";
        echo "<br>";
        echo "<input type='submit' value='Modifica'>";
        echo "<br>";
        echo "</form>";
        echo "<br>";
        echo "<a href='dba.php'>Torna alla home</a>";        
    ?>
</body>
</html>