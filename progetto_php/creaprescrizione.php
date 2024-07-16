<?php
    include_once 'database.php';
    include_once 'settings.php';
    session_start();
    // se non c'è un utente loggato e non c'è un cookie di login
    if (!isset($_SESSION['user']) && !isset($_COOKIE['user'])) {
        header('Location: login.php');
        exit();
    }
    // controllo che il tipo di utente sia Medico
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'Medico') {
        header('Location: index.php');
        exit();
    }
    // se arriva una richiesta POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table = 'prescrizione';
        $schema = get_schema($table);
        $values = [];
        foreach ($schema as $column) {
            // tolto il medico esterno perchè il medico è sempre il medico loggato
            if ($column == 'cfmedest') {
                $values['cfmedest'] = '';
                continue;
            }
            // mi salvo i valori passati in POST
            $values[$column] = $_POST[$column];
        }
        // inserisco nella tabella prescrizione i valori passati
        $arr = inserisci($table, $values);
        // gestione dell'errore
        if (!$arr[0]) {
            echo 'Errore: ' . $arr[1];
            exit();
        }
        // se tutto è andato a buon fine
        echo 'Inserimento avvenuto con successo';
        header('Refresh: 2; url = index.php');
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
        // prendo l'utente loggato
        $medico = get_user($_COOKIE['user']);
        $table = 'prescrizione';
        $schema = get_schema($table);
        // stampo la form utilizzando lo schema della tabella prescrizione
        echo '<form action="creaprescrizione.php" method="post">';
        echo '<h2> Inserimento in ' . $table . '</h2>';
        echo '<table>';
        foreach ($schema as $column) {
            // tolto il medico esterno perchè il medico è sempre il medico loggato
            if ($column == 'cfmedest') {
                continue;
            }
            echo '<tr>';
            echo '<td><label for="' . $column . '">' . $column . ' </label></td>';
            echo '<td><input type="text" id="' . $column . '" name="' . $column . '"';
            // nella colonna cfmedico metto il cf del medico loggato, immutabile
            if ($column == 'cfmedico') {
                echo ' value="' . $medico['cf'] . '" readonly';
            }
            // nella colonna data metto la data corrente, immutabile
            if ($column == 'data') {
                echo ' value="' . date('Y-m-d') . '" readonly';
            }
            echo '></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<br>';
        // quando schiaccio submit, invio i dati alla stessa pagina ed entra nel primo if
        echo '<input type="submit" value="Invia">';
        echo '</form>';
        echo '<br>';
        echo '<a href="index.php">Torna alla home</a>';
    ?>
</body>
</html>