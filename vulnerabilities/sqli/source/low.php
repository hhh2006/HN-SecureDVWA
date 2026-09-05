<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
    $id = trim((string)($_REQUEST['id'] ?? ''));

    if (dvwaSecurityContainsSqlInjection($id)) {
        dvwaSecurityEvent('SQL Injection', 'vulnerabilities/sqli', 'Blocked', 'Malicious SQL syntax detected');
        $html .= '<pre>Security control: SQL Injection attempt blocked.</pre>';
        return;
    }

    if ($id === '' || !ctype_digit($id)) {
        dvwaSecurityEvent('SQL Injection', 'vulnerabilities/sqli', 'Blocked', 'User ID must be a positive integer');
        $html .= '<pre>Security control: invalid User ID.</pre>';
        return;
    }

    $id = (int)$id;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],
                'SELECT first_name, last_name FROM users WHERE user_id = ?');
            if (!$stmt) { $html .= '<pre>Database error.</pre>'; break; }
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while( $row = mysqli_fetch_assoc( $result ) ) {
                $first = dvwaSecurityEscape($row['first_name']);
                $last  = dvwaSecurityEscape($row['last_name']);
                $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
            }
            mysqli_stmt_close($stmt);
            break;
        case SQLITE:
            global $sqlite_db_connection;
            $stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $results = $stmt->execute();
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $first = dvwaSecurityEscape($row['first_name']);
                $last  = dvwaSecurityEscape($row['last_name']);
                $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
            }
            break;
    }
}
?>
