<?php
    include_once 'settings.php';
    global $db_host, $db_name, $db_user, $db_pass, $db_port;
    // connessione al database
    $conn = pg_connect("host=$db_host dbname=$db_name user=$db_user password=$db_pass port=$db_port");

    if (!$conn) {
        echo "An error occurred.\n";
        exit();
    }
    // funzione che controlla la validità del login da login.php
    function check_login($username, $password) {
        global $conn;
        $query = "SELECT * FROM utenti WHERE username = $1 AND pass = $2";
        $result = pg_query_params($conn, $query, array($username, $password));
        // gestione dell'errore
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_num_rows($result) == 1;
    }
    // ritorna lo username
    function get_user($username) {
        global $conn;
        $query = "SELECT * FROM utenti WHERE username = $1";
        $result = pg_query_params($conn, $query, array($username));
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_assoc($result);
    }
    // stampa gli esami dal DB
    function get_esami() {
        global $conn;
        $query = "SELECT * FROM esame";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // stampa gli ospedali nel DB
    function get_ospedali() {
        global $conn;
        $query = "SELECT * FROM ospedale";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // stampa le info dell'esame dal suo codice
    function get_esame($id) {
        global $conn;
        $query = "SELECT * FROM esami WHERE codes = $1";
        $result = pg_query_params($conn, $query, array($id));
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_assoc($result);
    }

    //restituisce i nomi delle colonne della tabella
    function get_schema($table) {
        global $conn;
        $query = "SELECT column_name FROM information_schema.columns WHERE table_name = $1";
        $result = pg_query_params($conn, $query, array($table));

        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }

        return pg_fetch_all_columns($result);
    }
    // inserisce i valori nella tabella
    function inserisci($table, $values) {
        global $conn;
        // scrittura query INSERT INTO tabella(colonne) VALUES (valori) con lo stesso ordine
        $query = "INSERT INTO $table(";
        $schema = get_schema($table);
        foreach ($schema as $column) {
            $query .= $column . ", ";
        }
        $query = rtrim($query, ", ");
        $query .= ") VALUES(";
        foreach ($values as $value) {
            if ($value != '' && $value != 'null') {
                $query .= "'" . $value . "', ";
            } else {
                $query .= "NULL, ";
            }
        }
        $query = rtrim($query, ", ");
        $query .= ")";
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "An error occurred.\n";
            return array(false, pg_last_error($conn));
        }
        return array(true, "Insert successful");
    }
    // elimina una tupla dalla tabella
    function elimina($table, $values) {
        global $conn;
        $query = "DELETE FROM $table WHERE ";
        $schema = get_schema($table);
        $counter = 0;
        // scrittura query DELETE FROM tabella WHERE colonna = valore AND colonna = valore AND ...
        foreach ($schema as $atr) {
            $query .= $atr . " = ";
            if (isset($values[$counter]) & $values[$counter] != '') {
                $query .= "'" . $values[$counter] . "' AND ";
            } else {
                $query .= "NULL AND ";
            }
            $counter++;
        }
        $query = rtrim($query, "AND ");
        // esecuzione della query
        $result = pg_query($conn, $query);
        // gestione dell'errore
        if (!$result) {
            echo "An error occurred.\n";
            return array(false, pg_last_error($conn));
        }
        // se tutto è andato a buon fine
        return array(true, "Delete successful");
    }
    // restituisce le chiavi primarie della tabella
    function get_primary_keys($table) {
        $table = $_SESSION['tabella'];
        global $conn;
        $query = "SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
            FROM   pg_index i
            JOIN   pg_attribute a ON a.attrelid = i.indrelid
                     AND a.attnum = ANY(i.indkey)
            WHERE  i.indrelid = '{$table}'::regclass
        AND    i.indisprimary;";

        $result = pg_query($conn, $query);

        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }

        return pg_fetch_all_columns($result);
    }
    // modifica una tupla della tabella
    function modify($values) {
        global $conn;
        $table = $_SESSION['tabella'];
        $schema = get_schema($table);
        $query = "UPDATE $table SET ";
        // scrittura query UPDATE tabella SET colonna = valore, colonna = valore, ... WHERE chiave = valore AND chiave = valore AND ...
        foreach ($schema as $column) {
            $query .= $column . " = ";
            if (isset($values[$column]) & $values[$column] != '') {
                $query .= "'" . $values[$column] . "', ";
            } else {
                $query .= "NULL, ";
            }
        }
        $query = rtrim($query, ", ");
        $query .= " WHERE ";
        $chiavi = get_primary_keys($table);
        foreach ($chiavi as $chiave) {
            $query .= $chiave . " = ";
            if (isset($values[$chiave]) & $values[$chiave] != '') {
                $query .= "'" . $values[$chiave] . "' AND ";
            } else {
                $query .= "NULL AND ";
            }
        }
        $query = rtrim($query, "AND ");
        // esecuzione della query
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            return array(false, pg_last_error($conn));
        }
        return array(true, "Update successful");
    }
    // restituisce tutti i reparti
    function get_reparti() {
        global $conn;
        $query = "SELECT * FROM reparto";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce i medici in un reparto
    function get_medico_in_reparto($reparto) {
        global $conn;
        // join tra medico e reparto per ottenere i medici di un reparto
        $query = "SELECT m.cf, m.primario, m.vice FROM medico AS m JOIN reparto  AS r on m.codospedale = r.codospedale AND m.nomereparto = r.nome WHERE m.nomereparto = '{$reparto['nome']}' AND m.codospedale = {$reparto['codospedale']}";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce gli infermieri in un reparto
    function get_infermiere_in_reparto($reparto) {
        global $conn;
        // join tra infermiere e reparto per ottenere gli infermieri di un reparto
        $query = "SELECT i.cf FROM infermiere AS i JOIN reparto  AS r on i.codospedale = r.codospedale AND i.nomereparto = r.nome WHERE i.nomereparto = '{$reparto['nome']}' AND i.codospedale = {$reparto['codospedale']}";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce il personale amministrativo in un reparto
    function get_personaleamministrativo_in_reparto($reparto) {
        global $conn;
        // join tra personale amministrativo e reparto per ottenere il personale amministrativo di un reparto
        $query = "SELECT p.cf FROM personaleamministrativo AS p JOIN reparto  AS r on p.codospedale = r.codospedale AND p.nomereparto = r.nome WHERE p.nomereparto = '{$reparto['nome']}' AND p.codospedale = {$reparto['codospedale']}";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce un riparto data la sua posizione nella tabella
    function get_reparto_from_row_number($rownumber) {
        $reparti = get_reparti();
        $counter = 0;
        foreach ($reparti as $reparto) {
            if ($counter == $rownumber) {
                return $reparto;
            }
            $counter++;
        }
    }
    // restituisce la tabella dei ricoveri di un paziente
    function get_ricoveri($tesserasanitaria) {
        global $conn;
        // join tra ricovero e letto per ottenere i ricoveri di un paziente e le informazioni sull'ospedale
        $query = "SELECT * FROM ricovero as r JOIN letto as l ON r.codletto = l.codletto WHERE nTesSanitariaPaziente = $1";
        $result = pg_query_params($conn, $query, array($tesserasanitaria));
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce i viceprimari dato il loro numero di sostituzioni
    function get_vice_from_sostituzione($num) {
        global $conn;
        // se num = 0 devono fare una query diversa: stampo tutti i viceprimari tranne quelli in sostituzione
        // altrimenti uso GROUP BY e HAVING per ottenere i viceprimari con num sostituzioni
        if ($num == 0) {
            $query = "SELECT cf FROM medico WHERE vice = TRUE EXCEPT SELECT cfmedico1 FROM sostituzione";
        } else {
            $query = "SELECT cfmedico1 FROM sostituzione GROUP BY cfmedico1 HAVING COUNT(*) = {$num}";
        }
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce le prescrizioni effettuate da un medico
    function get_prescrizioni_from_medico($cf) {
        global $conn;
        $query = "SELECT * FROM prescrizione WHERE cfmedico = '{$cf}'";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce le prescrizioni di un paziente dato il suo numero di tessera sanitaria
    function get_prescrizioneConNome($tesseraS){
        global $conn;
        $query = "SELECT * FROM prescrizione where ntessanitariapaziente = $1"; // modifica
        $result = pg_query_params($conn, $query, array($tesseraS));
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce i laboratori interni
    function get_labinterno(){
        global $conn;
        $query = "SELECT * FROM laboratoriointerno";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
    // restituisce i laboratori esterni
    function get_labesterno(){
        global $conn;
        $query = "SELECT * FROM laboratorioesterno";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }   
    // elimina una prenotazione: utilizzata per paziente
    function eliminaprenotazione($tesseras, $datetimees,$codiceesame){
        global $conn;
        $query = "DELETE FROM prenotazione WHERE ntessanitariapaziente = '{$tesseras}' AND datetimees = '{$datetimees}' AND codiceesame = '{$codiceesame}'";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            return array(false, pg_last_error($conn));
        }
        return array(true, "Delete successful");
    }

    function isinPersonaleamministrativo($cf) {
        global $conn;
        $query = "SELECT * FROM personaleamministrativo WHERE cf = '{$cf}'";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_num_rows($result) == 1;
    }


function getospedalefromcf($cf){
    global $conn;
    $query = "SELECT codospedale FROM personaleamministrativo WHERE cf = '{$cf}'";
    $result = pg_query($conn, $query);
    if (!$result) {
        echo "An error occurred.\n";
        echo pg_last_error($conn);
        exit();
    }
    $row = pg_fetch_assoc($result);
    return $row['codospedale'];
}
    
    function get_reparti_from_ospedale($codospedale){
        global $conn;
        $query = "SELECT * FROM reparto WHERE codospedale = {$codospedale}";
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }

    function get_vice_from_sostituzione_and_ospedale($num, $codospedale){
        global $conn;
        if ($num == 0) {
            $query = "SELECT cf FROM medico WHERE vice = TRUE AND codospedale = {$codospedale} EXCEPT SELECT cfmedico1 FROM sostituzione";
        } else {
            $query = "SELECT cfmedico1 FROM sostituzione JOIN medico on medico.cf = sostituzione.cfmedico1 WHERE codospedale = {$codospedale} GROUP BY cfmedico1 HAVING COUNT(*) = {$num}";
        }
        $result = pg_query($conn, $query);
        if (!$result) {
            echo "An error occurred.\n";
            echo pg_last_error($conn);
            exit();
        }
        return pg_fetch_all($result);
    }
?>