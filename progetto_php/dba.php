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
    
    // se arriva una richiesta di post attraverso Gestione tabelle
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['tabelle']) && isset($_POST['opzioni'])) {
            // salvo in sessione la tabella e l'opzione scelta
            $_SESSION['tabella'] = $_POST['tabelle'];
            $_SESSION['opzione'] = $_POST['opzioni'];

            $opzione = $_POST['opzioni'];
            // reindirizzo alla pagina corretta
            switch ($opzione) {
                case 'Inserisci':
                    header('Location: inserisci.php');
                    exit();
                case 'Modifica':
                    header('Location: modifica.php');
                    exit();
                case 'Eliminazione':
                    header('Location: elimina.php');
                    exit();
                default:
                    break;
            }
        }
        // se arriva una richiesta di post attraverso Visualizzazione Personale nel Reparto
        if (isset($_POST['reparto'])) {
            $reparto = $_POST['reparto'];
            $_SESSION['reparto'] = $reparto;
            header('Location: visualizza.php');
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DBA</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2> Gestione tabelle </h2>
    <form action="dba.php" method='post'>
        <label for="opzioni"></label>
        <select name="tabelle" id="tabelle">
            <option value="ospedale">Ospedale</option> 
            <option value="esame">Esame</option>
            <option value="infermiere">Infermiere</option>
            <option value="medico">Medico</option>
            <option value="paziente">Paziente</option>
            <option value="personaleamministrativo">Personale Amministrativo</option>
            <option value="prenotazione">Prenotazione</option>
            <option value="reparto">Reparto</option>
        </select>
        <select id="opzioni" name="opzioni" required>
            <option value="Inserisci">Inserimento</option>
            <option value="Modifica">Modifica</option>
            <option value="Eliminazione">Eliminazione</option>
        </select>
        <input type="submit" value="Invia">
    </form>

    <h2> Visualizzazioni:</h2>
    <h3> Personale nel Reparto </h3>
    <form action="dba.php" method="post">
        <select name="reparto" id="reparto">
            <?php
                // inserisco tutti i reparti in un select
                $reparti = get_reparti();
                $contatore = 0;
                foreach ($reparti as $reparto) {
                    echo '<option value="' . $contatore . '">' . $reparto['nome'] . ' Ospedale: ' . $reparto['codospedale'] .'</option>';
                    $contatore++;
                }
            ?>
        </select>
        <input type="submit" value="Visualizza">
    </form>

    <h2> Visualizzazione Vice-Primari </h2>
    <form action="dba.php" method="post">
        <label for="numero">Numero di sostituzioni</label>
        <input type="number" name="numero" min="0"id="numero" required>
        <input type="submit" value="Visualizza">
    </form>
    <?php
        // se arriva una richiesta di post attraverso Visualizzazione Vice-Primari
        if (isset($_POST['numero'])) {
            $numero = $_POST['numero'];
            $viceprimari = get_vice_from_sostituzione($numero);
            // se non ci sono viceprimari con il numero di sostituzioni inserito stampo un messaggio, altrimenti stampo i viceprimari
            if ($viceprimari == null) {
                echo '<h3> Nessun Vice-Primario con ' . $numero . ' sostituzioni</h3>';
                
            } else {
                echo '<h3> Vice-Primari con ' . $numero . ' sostituzioni</h3>';
                echo '<table>';
                echo '<tr>';
                echo '<th>CF</th>';
                echo '</tr>';
                // stampo i viceprimari
                foreach ($viceprimari as $viceprimario) {
                    echo '<tr>';
                    if (isset($viceprimario['cf'])) {
                        echo '<td>' . $viceprimario['cf'] . '</td>';
                    } else {
                        echo '<td>' . $viceprimario['cfmedico1'] . '</td>'; 
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
    ?>
    <br>
    <a href="index.php">Torna indietro</a>
</body>
</html>