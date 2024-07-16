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
    // dopo aver selezionato la tupla da eliminare, la elimino
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        // se non è stata fatta una scelta reindirizzo alla pagina di eliminazione
        if (!isset($_POST['selectedRow'])) {
            header('Location: elimina.php');
            exit();
        }   
        $selectedRow = $_POST['selectedRow'];
        $query = "SELECT * FROM $table";
        $result = pg_query($query);
        $counter = 0;
        // abbiamo fatto un po' uno sgamo: in pratica stampiamo ancora la tabella ed eliminiamo quello con lo stesso indice
        while ($row = pg_fetch_assoc($result)) {
            if ($counter == $selectedRow) {
                $values = [];
                $counter = 0;
                foreach ($schema as $column) {
                    // prendo i valori della tupla selezionata
                    $values[$counter] = $row[$column];
                    $counter++;
                }
                // elimino la tupla
                $arr = elimina($table, $values);
                if (!$arr[0]) {
                    echo 'Errore: ' . $arr[1];
                    exit();
                }
                $_SESSION['tabella'] = "";
                echo 'Eliminazione avvenuta con successo';
                header('Refresh: 2; url = dba.php');
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
    <title>Elimina</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        // porto la tabella e la sua intestazione
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        echo "<h2>Elimina in $table</h2>";
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
        echo "<form action='elimina.php' method='post'>";
        // Create the table rows
        $_SESSION['values'] = [];
        $counter = 0;
        while ($row = pg_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($schema as $columnName) {
                echo "<td>" . $row[$columnName] . "</td>";
            }
            echo "<td><input type='radio' name='selectedRow' value='{$counter}'></td>";
            $counter++; // Add the radio button for selection
            echo "</tr>";
        }
        echo "</table>";
        echo "<br>";
        echo "<input type='submit' value='Elimina'>";
        echo "<br>";
        echo "</form>";
        echo "<br>";
        echo "<a href='dba.php'>Torna alla Home</a>";
    ?>
</body>
</html>