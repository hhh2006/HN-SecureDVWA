<?php

header ('X-XSS-Protection: 0');

if( array_key_exists( 'name', $_GET ) && $_GET['name'] !== NULL ) {
    $name = (string)$_GET['name'];
    if (dvwaSecurityContainsXss($name)) {
        dvwaSecurityEvent('Reflected XSS', 'vulnerabilities/xss_r', 'Blocked', 'HTML/script payload detected');
        $html .= '<pre>Security control: Reflected XSS attempt blocked.</pre>';
    }
    else {
        $html .= '<pre>Hello ' . dvwaSecurityEscape($name) . '</pre>';
    }
}
?>
