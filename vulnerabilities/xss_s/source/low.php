<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
    $message = trim((string)($_POST['mtxMessage'] ?? ''));
    $name    = trim((string)($_POST['txtName'] ?? ''));

    if (dvwaSecurityContainsXss($message) || dvwaSecurityContainsXss($name)) {
        dvwaSecurityEvent('Stored XSS', 'vulnerabilities/xss_s', 'Blocked', 'HTML/script payload detected');
        $html .= '<pre>Security control: Stored XSS attempt blocked. Nothing was stored.</pre>';
        return;
    }

    $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"],
        'INSERT INTO guestbook (comment, name) VALUES (?, ?)');
    if (!$stmt) {
        $html .= '<pre>Database error.</pre>';
        return;
    }
    mysqli_stmt_bind_param($stmt, 'ss', $message, $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
