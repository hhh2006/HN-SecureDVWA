<?php

$dom_default = 'English';
if (array_key_exists('default', $_GET)) {
    $candidate = (string)$_GET['default'];
    $allowed = array('English', 'French', 'Spanish', 'German');
    if (in_array($candidate, $allowed, true)) {
        $dom_default = $candidate;
    } else {
        if (dvwaSecurityContainsXss($candidate) || !in_array($candidate, $allowed, true)) {
            dvwaSecurityEvent('DOM XSS', 'vulnerabilities/xss_d', 'Blocked', 'Language parameter rejected by allowlist');
        }
    }
}
?>
