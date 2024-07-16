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
    $_SESSION['tabella'] = 'prenotazione';

?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Visualizza Prenotazioni</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <?php
            $table = $_SESSION['tabella'];
            $schema = get_schema($table);
            echo "<h2>Visualizza prenotazioni</h2>";
            $query = "SELECT * FROM prenotazione where ntessanitariapaziente = $1";
            $result = pg_query_params($query, array($_SESSION['tesserasanitaria']));
            echo "<table>";
            echo "<tr>";
             // Add the column header for selection
            foreach ($schema as $columnName) {
                if ($columnName == 'ntessanitariapaziente' ) {
                    continue;
                }
                echo "<th>$columnName</th>";
            }
            echo "</tr>";
            // Create the table rows
            $_SESSION['values'] = [];
            $counter = 0;
            while ($row = pg_fetch_assoc($result)) {
                echo "<tr>";
                foreach ($schema as $columnName) {
                    if ($columnName == 'ntessanitariapaziente' ) {
                        continue;
                    }
                    echo "<td>" . $row[$columnName] . "</td>";
                }
                $counter++; // Add the radio button for selection
                echo "</tr>";
            }
            echo "</table>";
            
        ?>
        <br>
        <a href="index.php">Torna alla home</a>
    </body>
</html>