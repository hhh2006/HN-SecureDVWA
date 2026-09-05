<?php

if( isset( $_GET[ 'Submit' ] ) ) {
    $id = trim((string)($_GET['id'] ?? ''));
    $exists = false;

    if (dvwaSecurityContainsSqlInjection($id)) {
        dvwaSecurityEvent('Blind SQL Injection', 'vulnerabilities/sqli_blind', 'Blocked', 'Malicious SQL syntax detected');
        $html .= '<pre>Security control: Blind SQL Injection attempt blocked.</pre>';
        return;
    }

    if ($id === '' || !ctype_digit($id)) {
        dvwaSecurityEvent('Blind SQL Injection', 'vulnerabilities/sqli_blind', 'Blocked', 'User ID must be a positive integer');
        $html .= '<pre>Security control: invalid User ID.</pre>';
        return;
    }
    $id = (int)$id;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],
                'SELECT first_name, last_name FROM users WHERE user_id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $exists = $result && mysqli_num_rows($result) > 0;
                mysqli_stmt_close($stmt);
            }
            break;
        case SQLITE:
            global $sqlite_db_connection;
            $stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
            $exists = $row !== false;
            break;
    }

    if ($exists) {
        $html .= '<pre>User ID exists in the database.</pre>';
    } else {
        header( $_SERVER[ 'SERVER_PROTOCOL' ] . ' 404 Not Found' );
        $html .= '<pre>User ID is MISSING from the database.</pre>';
    }
}
?>
