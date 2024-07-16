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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table = 'prenotazione';
        $schema = get_schema($table);
        $values = [];
        foreach ($schema as $column) {
            // il numero di tessera sanitaria è quello del paziente loggato
            if ($column == 'ntessanitariapaziente') {
                $values[$column] = $_SESSION['tesserasanitaria'];
                continue;
            }
            // setto di base regime a vero, poichè non so se viene controllato prima o dopo il compo prescrizione
            if ($column == 'regime') {
                $values[$column] = 't';
                continue;
            }
            // se il campo è datetimees, lo setto in modo corretto, tolgo la T e aggiungo :00
            if ($column == 'datetimees') {
                $values[$column] = str_replace('T', ' ',$_POST[$column].":00");
                continue;
            }
            // la data di prenotazione è quella odierna
            if ($column == 'dataprenotazione') {
                $values[$column] = date('Y-m-d');
                continue;
            }
            // gli altri li setto normali: quelli inviati dall'utente
            $values[$column] = $_POST[$column];
        }
        // a questo punto controllo se il campo prescrizione è null, se lo è setto regime a falso
        if ($values['codprescrizione'] == 'null') {
            $values['regime'] = 'f';
        }
        // inserisco la prenotazione
        $arr = inserisci($table, $values);
        if (!$arr[0]) {
            echo 'Errore: ' . $arr[1];
            exit();
        }
        echo 'Inserimento avvenuto con successo';
        header('Refresh: 2; url = index.php');
        exit();
    }
    
?>
<!doctype html>
<html>
    <head>
        <title>Prenota</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <!-- form per la prenotazione di un esame -->
        <h2>Prenota Esame</h2>
        <form method="POST" action="prenota.php">
            <table>
                <tr>
                    <td>
                        <label for="codiceesame">Esame</label>
                    </td>
                    <td>
                        <select name="codiceesame" id="codiceesame" required>
                            <?php
                                echo '<option value="null">Nessun esame</option>';
                                $esami = get_esami();
                                // stampo tutti gli esami usando come value il loro codice
                                foreach ($esami as $esame) {
                                    echo '<option value="' . $esame['codes'] . '">' . $esame['descrizione'] . '</option>';
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="datatimees">Data e ora esame</label>
                    </td>
                    <td>
                        <input type="datetime-local" id="datetimees" name="datetimees" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="codprescrizione">Codice prescrizione</label>
                    </td>
                    <td>
                        <select name="codprescrizione" id="codprescrizione">
                            <option value="null">Nessuna prescrizione</option>
                            <?php
                                $prescrizioni = get_prescrizioneconNome($_SESSION['tesserasanitaria']);
                                foreach ($prescrizioni as $prescrizione) {
                                    echo '<option value="' . $prescrizione['codice'] . '">' . $prescrizione['codice'] . '</option>';
                                }
                            ?>
                        </select>
                    </td>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="codlabint">Laboratorio interno</label>
                    </td>
                    <td>
                        <select name="codlabint" id="codlabint">
                            <option value="null">Nessun laboratorio interno</option>
                            <?php
                                $labinterni = get_labinterno();
                                // stampo tutti i laboratori interni usando come value il loro codice
                                foreach ($labinterni as $labinterno) {
                                    echo '<option value="' . $labinterno['codice'] . '">' . $labinterno['pianostanza'] . ' ' . $labinterno['numerostanza'] . ' ' . $labinterno['nomereparto'] . ' ' . $labinterno['codospedale'] .  '</option>';
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="codlabest">Laboratorio esterno</label>
                    </td>
                    <td>
                        <select name="codlabest" id="codlabest">
                            <option value="null">Nessun laboratorio esterno</option>
                            <?php
                                $labesterni = get_labesterno();
                                // stampo tutti i laboratori esterni usando come value il loro codice
                                foreach ($labesterni as $labesterno) {
                                    echo '<option value="' . $labesterno['codice'] . '">' . $labesterno['indirizzo'] . ' ' . $labesterno['telefono'] . '</option>';
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="urgenza">Urgenza</label>
                    </td>
                    <td>
                        <select name="urgenza" id="urgenza" required>
                            <option value="Verde">Verde</option>
                            <option value="Giallo">Giallo</option>
                            <option value="Rosso">Rosso</option>
                        </select>
                    </td>
                </tr>
            </table>
            <br>
            <input type="submit" value="Prenota">
        </form>
        <br>
        <a href="index.php">Torna alla home</a>
        
    </body>
</html>