<?php
    include_once 'settings.php';
    include_once 'database.php';
    session_start();
    // se non è loggato nessun utente o non è settato il cookie di login reindirizzo alla pagina di login
    if(!isset($_SESSION['user']) && !isset($_COOKIE['user'])){
        header('Location: login.php');
        exit();
    }
    // se il tipo di utente non è Paziente reindirizzo alla pagina principale
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'Paziente') {
        header('Location: index.php');
        exit();
    }
    // dopo aver selezionato la tupla da eliminare, la elimino. Faccio la stessa cosa che in elimna.php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table = 'prenotazione';
        $tesseraS = $_SESSION['tesserasanitaria'];
        $schema = get_schema($table);
        $selectedRow = $_POST['selectedRow'];
        $query = "SELECT * FROM $table where ntessanitariapaziente = $1";
        $result = pg_query_params($query, array($tesseraS));
        $counter = 0;
        while ($row = pg_fetch_assoc($result)) {
            if ($counter == $selectedRow) {
                $tesseras = $row['ntessanitariapaziente'];
                $datetimees = $row['datetimees'];
                $codiceesame = $row['codiceesame'];
                $arr = eliminaprenotazione($tesseras, $datetimees,$codiceesame);
                if (!$arr[0]) {
                    echo 'Errore: ' . $arr[1];
                    exit();
                }else{
                    echo 'Eliminazione avvenuta con successo';
                    $_SESSION['tabella'] = "";
                    header('Refresh: 2; url = index.php');
                    exit();
                }
            }
            $counter++;
        }
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Elimina</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        
        <?php

            $schema = get_schema('prenotazione');
            echo "<h2>Elimina prenotazioni</h2>";
            $query = "SELECT * FROM prenotazione where ntessanitariapaziente = $1";
            $result = pg_query_params($conn, $query, array($_SESSION['tesserasanitaria']));

            echo "<table>";

            echo "<tr>";
            // Add the column header for selection
            foreach ($schema as $columnName) {
                echo "<th>$columnName</th>";
            }
            echo "<th>Select</th>";
            echo "</tr>";
            echo "<form action='eliminaprenotazione.php' method='post'>";
            // Create the table rows
            $_SESSION['values'] = [];
            $counter = 0;
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                foreach ($schema as $columnName) {
                    echo "<td>" . $row[$columnName] . "</td>";
                }
                echo "<td><input type='radio' name='selectedRow' value='{$counter}'> </td>";
                $counter++; // Add the radio button for selection
                echo "</tr>";
            }
            echo "</table>";
            echo "<br>";
            echo "<input type='submit' value='Elimina'>";
            echo "<br>";
            echo "</form>";
            echo '<a href=index.php><br>Torna alla home</a>';
        ?>
    </body>
</html>