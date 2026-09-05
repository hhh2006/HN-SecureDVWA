<?php
// HN SecureDVWA local security regression tests.
// These tests exercise the reusable security controls without attacking any external system.
require_once __DIR__ . '/../dvwa/includes/security.php';

$tests = [];
function t($name, $ok, $detail='') { global $tests; $tests[] = [$name, (bool)$ok, $detail]; }

t('XSS detector blocks script', dvwaSecurityContainsXss("<script>alert(1)</script>"));
t('XSS detector blocks event handler', dvwaSecurityContainsXss('<img src=x onerror=alert(1)>'));
t('XSS detector allows ordinary text', !dvwaSecurityContainsXss('Hello DVWA'));

t('SQLi detector blocks boolean OR', dvwaSecurityContainsSqlInjection("1' OR '1'='1' #"));
t('SQLi detector blocks UNION SELECT', dvwaSecurityContainsSqlInjection("1 UNION SELECT user,password FROM users"));
t('SQLi detector allows numeric ID', !dvwaSecurityContainsSqlInjection('7'));

t('Command detector blocks shell separator', dvwaSecurityContainsCommandInjection('127.0.0.1 | whoami'));
t('Command detector blocks ampersand', dvwaSecurityContainsCommandInjection('127.0.0.1 & id'));
t('Command detector allows normal IPv4', !dvwaSecurityContainsCommandInjection('127.0.0.1'));

t('Output escaping encodes angle brackets', dvwaSecurityEscape('<tag>') === '&lt;tag&gt;');
t('Output escaping encodes quotes', dvwaSecurityEscape('"test"') === '&quot;test&quot;');

$root = realpath(__DIR__ . '/..');
$sourceChecks = [
    'SQLi prepared statement' => 'vulnerabilities/sqli/source/low.php',
    'Blind SQLi prepared statement' => 'vulnerabilities/sqli_blind/source/low.php',
    'Command Injection shell escaping' => 'vulnerabilities/exec/source/low.php',
    'Reflected XSS output encoding' => 'vulnerabilities/xss_r/source/low.php',
    'DOM XSS safe server-rendering' => 'vulnerabilities/xss_d/index.php',
    'Stored XSS output encoding' => 'dvwa/includes/dvwaPage.inc.php',
    'File Upload MIME validation' => 'vulnerabilities/upload/source/low.php',
];
foreach ($sourceChecks as $label => $file) {
    $path = $root . DIRECTORY_SEPARATOR . $file;
    $src = is_file($path) ? file_get_contents($path) : '';
    $needle = match (true) {
        str_contains($label, 'SQLi prepared') && str_contains($label, 'Blind') => 'mysqli_prepare',
        str_contains($label, 'SQLi prepared') => 'mysqli_prepare',
        str_contains($label, 'shell') => 'escapeshellarg',
        str_contains($label, 'Reflected') => 'dvwaSecurityEscape',
        str_contains($label, 'DOM') => 'dvwaSecurityEscape',
        str_contains($label, 'Stored') => 'htmlspecialchars',
        str_contains($label, 'MIME') => 'new finfo',
        default => '',
    };
    $ok = $needle !== '' && strpos($src, $needle) !== false;
    if (str_contains($label, 'DOM')) { $ok = $ok && strpos($src, 'document.write') === false; }
    t($label, $ok, $file);
}

$passed = 0;
foreach ($tests as [$name, $ok, $detail]) {
    echo ($ok ? "PASS" : "FAIL") . " | " . $name . ($detail !== '' ? " | {$detail}" : '') . PHP_EOL;
    if ($ok) $passed++;
}
$failed = count($tests) - $passed;
echo PHP_EOL . "RESULT: {$passed}/" . count($tests) . " PASS" . PHP_EOL;
exit($failed === 0 ? 0 : 1);
