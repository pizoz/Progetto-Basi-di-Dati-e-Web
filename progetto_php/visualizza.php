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
    // se il tipo di utente non è DBA reindirizzo alla pagina principale
    $tipoUtente = $_SESSION['tipoUtente'];
    if ($tipoUtente != 'DBA' && $tipoUtente != 'Personale amministrativo'){
        header('Location: index.php');
        exit();
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizzazione</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php
        // mi porto il reparto data il suo indice
        $reparto = get_reparto_from_row_number($_SESSION['reparto']);
        echo "<h2>". $reparto['nome'] . "</h2>";
        // mi porto i medici del reparto selezionato
        $medici = get_medico_in_reparto($reparto);
        echo "<h3>Medici</h3>";
        echo "<table>";
        echo "<tr>";
        echo "<th>CF</th>";
        echo "<th>Primario</th>";
        echo "<th>Vice</th>";
        echo "</tr>";
        // stampo i medici
        foreach ($medici as $medico) {
            echo "<tr>";
            echo "<td>" . $medico['cf'] . "</td>";
            echo "<td>" . $medico['primario'] . "</td>";
            echo "<td>" . $medico['vice'] . "</td>";
            echo "</tr>";
        }
        // mi porto gli infermieri del reparto selezionato
        $infermieri = get_infermiere_in_reparto($reparto);
        echo "</table>";
        echo "<h3>Infermieri </h3>";
        echo "<table>";
        echo "<tr>";
        echo "<th>CF</th>";
        echo "</tr>";
        // stampo gli infermieri
        foreach ($infermieri as $infermiere) {
            echo "<tr>";
            echo "<td>" . $infermiere['cf'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo '<br>';
        echo '<a href="dba.php"> Torna alla home</a>'
    ?>
</body>
</html>