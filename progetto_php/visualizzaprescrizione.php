<?php
    include_once 'database.php';
    include_once 'settings.php';
    session_start();
    // se non c'è un utente loggato e non c'è un cookie di login
    if (!isset($_SESSION['user']) && !isset($_COOKIE['user'])) {
        header('Location: login.php');
        exit();
    }
    // se il tipo di utente non è Medico reindirizzo alla pagina principale
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'Medico') {
        header('Location: index.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescrizioni</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        // mi porto il codice fiscale dalla tabella utenti del DB
        $cf = $_SESSION['cf'];
        // mi porto lo schema della tabella prescrizione
        $schema = get_schema('prescrizione');
        // mi porto le prescrizioni del medico
        $prescrizioni = get_prescrizioni_from_medico($cf);
        echo '<h2> Prescrizioni </h2>';
        echo '<table>';
        echo '<tr>';
        // stampo l'intestazione senza stampare i campi cfmedico e cfmedest perchè il cf è del medico loggato
        foreach ($schema as $column) {
            if ($column === 'cfmedico' || $column === 'cfmedest') {
                continue;
            }
            echo '<th>' . $column . '</th>';
        }
        echo '</tr>';
        // stampo le prescrizioni senza stampare i campi cfmedico e cfmedest
        foreach ($prescrizioni as $prescrizione) {
            echo '<tr>';
            foreach ($schema as $column) {
                if ($column === 'cfmedico' || $column === 'cfmedest') {
                    continue;
                }
                echo '<td>' . $prescrizione[$column] . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '<br>';
        echo '<a href="index.php">Torna alla home</a>';
    ?>
</body>
</html>