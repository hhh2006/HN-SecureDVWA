<?php

if( isset( $_POST[ 'Submit' ] ) ) {
    $target = trim((string)($_POST['ip'] ?? $_REQUEST['ip'] ?? ''));

    if (dvwaSecurityContainsCommandInjection($target)) {
        dvwaSecurityEvent('Command Injection', 'vulnerabilities/exec', 'Blocked', 'Command separator or shell token detected');
        $html .= '<pre>Security control: Command Injection attempt blocked.</pre>';
        return;
    }

    if (filter_var($target, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
        dvwaSecurityEvent('Command Injection', 'vulnerabilities/exec', 'Blocked', 'Input is not a valid IPv4 address');
        $html .= '<pre>Security control: only a valid IPv4 address is accepted.</pre>';
        return;
    }

    $safeTarget = escapeshellarg($target);
    if( stristr( php_uname( 's' ), 'Windows NT' ) ) {
        $cmd = shell_exec( 'ping ' . $safeTarget );
    }
    else {
        $cmd = shell_exec( 'ping -c 4 ' . $safeTarget );
    }
    $html .= '<pre>' . dvwaSecurityEscape($cmd) . '</pre>';
}
?>
